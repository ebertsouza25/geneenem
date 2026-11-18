<?php
// Operações do aluno no banco: conta, simulados e indicadores do perfil.
// Todas as consultas usam prepare/bind para não montar SQL com dado digitado.

class Aluno
{
    private mysqli $mysql;

    public function __construct(mysqli $mysql)
    {
        $this->mysql = $mysql;
    }

    // Cria a conta e devolve o id. Se o e-mail já existir, devolve null
    // (o formulário mostra a mensagem de e-mail em uso).
    public function cadastrar(string $nome, string $email, string $senha): ?int
    {
        $sql = "INSERT INTO alunos (nome, email, senha_hash) VALUES (?, ?, ?)";
        $stmt = $this->mysql->prepare($sql);

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt->bind_param("sss", $nome, $email, $senhaHash);

        try {
            $stmt->execute();
            $id = (int) $this->mysql->insert_id;
            $stmt->close();
            return $id;
        } catch (mysqli_sql_exception $erro) {
            $stmt->close();

            // 1062 = chave duplicada (e-mail já cadastrado).
            if ($erro->getCode() === 1062) {
                return null;
            }

            throw $erro;
        }
    }

    // Confere e-mail e senha. Devolve os dados públicos do aluno ou null.
    public function autenticar(string $email, string $senha): ?array
    {
        $sql = "SELECT id, nome, email, senha_hash FROM alunos WHERE email = ? LIMIT 1";
        $stmt = $this->mysql->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $aluno = $resultado->fetch_assoc();
        $stmt->close();

        if (!$aluno) {
            return null;
        }

        if (!password_verify($senha, $aluno['senha_hash'])) {
            return null;
        }

        // O hash não precisa sair daqui.
        unset($aluno['senha_hash']);
        return $aluno;
    }

    public function buscar(int $alunoId): ?array
    {
        $sql = "SELECT id, nome, email, criado_em FROM alunos WHERE id = ? LIMIT 1";
        $stmt = $this->mysql->prepare($sql);
        $stmt->bind_param("i", $alunoId);
        $stmt->execute();
        $aluno = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $aluno ?: null;
    }

    // Grava um simulado concluído e as respostas de cada questão.
    // O token único evita gravar o mesmo envio duas vezes.
    // Retorno: 'gravado', 'duplicado' ou 'erro'.
    public function registrarAtividade(int $alunoId, string $token, array $respostas, string $tipo = 'simulado_enem'): string
    {
        $total = count($respostas);

        if ($total === 0) {
            return 'erro';
        }

        $acertos = 0;

        foreach ($respostas as $resposta) {
            if (!empty($resposta['acertou'])) {
                $acertos++;
            }
        }

        $erros = $total - $acertos;
        $percentual = round($acertos * 100 / $total, 2);

        $sqlAtividade = "INSERT INTO atividades
            (aluno_id, token, tipo, total_questoes, acertos, erros, pontuacao, percentual)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmtAtividade = $this->mysql->prepare($sqlAtividade);
        $stmtAtividade->bind_param(
            "issiiiid",
            $alunoId,
            $token,
            $tipo,
            $total,
            $acertos,
            $erros,
            $acertos,
            $percentual
        );

        // A transação garante que atividade e respostas entram juntas.
        $this->mysql->begin_transaction();

        try {
            $stmtAtividade->execute();
            $atividadeId = (int) $this->mysql->insert_id;
            $stmtAtividade->close();

            $sqlResposta = "INSERT INTO respostas
                (atividade_id, numero_questao, area, resposta, gabarito, acertou)
                VALUES (?, ?, ?, ?, ?, ?)";

            $stmtResposta = $this->mysql->prepare($sqlResposta);

            foreach ($respostas as $numeroQuestao => $resposta) {
                $numero = (int) $numeroQuestao;
                $area = (string) $resposta['area'];
                $marcada = (string) $resposta['resposta'];
                $gabarito = (string) $resposta['gabarito'];
                $acertou = !empty($resposta['acertou']) ? 1 : 0;

                $stmtResposta->bind_param("iisssi", $atividadeId, $numero, $area, $marcada, $gabarito, $acertou);
                $stmtResposta->execute();
            }

            $stmtResposta->close();
            $this->mysql->commit();
            return 'gravado';
        } catch (mysqli_sql_exception $erro) {
            $this->mysql->rollback();

            // Token repetido: o resultado já tinha sido salvo, então não é
            // um erro para o aluno.
            if ($erro->getCode() === 1062) {
                return 'duplicado';
            }

            error_log('GeneEnem: falha ao registrar atividade: ' . $erro->getMessage());
            return 'erro';
        }
    }

    // Números gerais do perfil, calculados só com as atividades do aluno.
    public function resumo(int $alunoId): array
    {
        $sql = "SELECT
                    COUNT(*) AS simulados,
                    COALESCE(SUM(total_questoes), 0) AS questoes,
                    COALESCE(SUM(acertos), 0) AS acertos,
                    COALESCE(SUM(erros), 0) AS erros,
                    COALESCE(SUM(pontuacao), 0) AS pontuacao
                FROM atividades
                WHERE aluno_id = ?";

        $stmt = $this->mysql->prepare($sql);
        $stmt->bind_param("i", $alunoId);
        $stmt->execute();
        $resumo = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $questoes = (int) $resumo['questoes'];

        $resumo['simulados'] = (int) $resumo['simulados'];
        $resumo['questoes'] = $questoes;
        $resumo['acertos'] = (int) $resumo['acertos'];
        $resumo['erros'] = (int) $resumo['erros'];
        $resumo['pontuacao'] = (int) $resumo['pontuacao'];
        $resumo['aproveitamento'] = $questoes > 0
            ? (int) round($resumo['acertos'] * 100 / $questoes)
            : 0;

        return $resumo;
    }

    // Últimos simulados, do mais novo para o mais antigo.
    public function historico(int $alunoId, int $limite = 20): array
    {
        $sql = "SELECT id, tipo, total_questoes, acertos, erros, pontuacao, percentual, criado_em
                FROM atividades
                WHERE aluno_id = ?
                ORDER BY criado_em DESC, id DESC
                LIMIT ?";

        $stmt = $this->mysql->prepare($sql);
        $stmt->bind_param("ii", $alunoId, $limite);
        $stmt->execute();
        $historico = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $historico;
    }

    // Acertos e total por área, para mostrar onde o aluno precisa reforçar.
    public function desempenhoPorArea(int $alunoId): array
    {
        $sql = "SELECT r.area,
                       COUNT(*) AS total,
                       SUM(r.acertou) AS acertos
                FROM respostas r
                INNER JOIN atividades a ON a.id = r.atividade_id
                WHERE a.aluno_id = ?
                GROUP BY r.area
                ORDER BY total DESC, r.area ASC";

        $stmt = $this->mysql->prepare($sql);
        $stmt->bind_param("i", $alunoId);
        $stmt->execute();
        $areas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($areas as $indice => $area) {
            $total = (int) $area['total'];
            $areas[$indice]['total'] = $total;
            $areas[$indice]['acertos'] = (int) $area['acertos'];
            $areas[$indice]['percentual'] = $total > 0
                ? (int) round($areas[$indice]['acertos'] * 100 / $total)
                : 0;
        }

        return $areas;
    }
}
