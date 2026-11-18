<?php
// Perfil do aluno. Tudo é consultado pelo id guardado na sessão: a página não
// aceita id pela URL, então ninguém abre o histórico de outra pessoa.

require_once 'funcoes.php';
require_once 'config.php';
require_once 'Aluno.php';

iniciarSessao();

if (!isset($_SESSION['aluno_id'])) {
    header('Location: login.php');
    exit;
}

$alunoObj = new Aluno($conexao);
$aluno = $alunoObj->buscar((int) $_SESSION['aluno_id']);

// Conta removida ou sessão antiga sem aluno no banco: volta para o login.
if ($aluno === null) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

$resumo = $alunoObj->resumo((int) $aluno['id']);
$historico = $alunoObj->historico((int) $aluno['id'], 20);
$areas = $alunoObj->desempenhoPorArea((int) $aluno['id']);

// A evolução mostra os últimos simulados na ordem em que aconteceram.
$evolucao = array_reverse(array_slice($historico, 0, 8));

$pagina = 'perfil';
$tituloPagina = 'Meu perfil';
$cssPagina = array('perfil');

require_once 'topo.php';
?>

<section class="page-head">
    <div class="wrap">
        <span class="eyebrow">Meu perfil · <?php echo escaparHtml($aluno['nome']); ?></span>
        <h1>Sua evolução em Genética</h1>
        <p class="lead">
            Os números abaixo vêm dos simulados que você concluiu. Cada novo
            resultado entra aqui automaticamente.
        </p>

        <div class="perfil-conta">
            <span class="user-avatar"><?php echo escaparHtml($_SESSION['inicial']); ?></span>
            <span>
                <strong><?php echo escaparHtml($aluno['nome']); ?></strong>
                <small><?php echo escaparHtml($aluno['email']); ?> · desde <?php echo date('d/m/Y', strtotime($aluno['criado_em'])); ?></small>
            </span>
        </div>
    </div>
</section>

<section class="section perfil-secao">
    <div class="wrap">

        <!-- Indicadores gerais -->
        <div class="perfil-grid">
            <article class="perfil-stat">
                <span>Questões respondidas</span>
                <strong><?php echo $resumo['questoes']; ?></strong>
                <small>em <?php echo $resumo['simulados']; ?> simulado<?php echo $resumo['simulados'] === 1 ? '' : 's'; ?></small>
            </article>

            <article class="perfil-stat perfil-stat-ok">
                <span>Acertos</span>
                <strong><?php echo $resumo['acertos']; ?></strong>
                <small>respostas corretas</small>
            </article>

            <article class="perfil-stat perfil-stat-erro">
                <span>Erros</span>
                <strong><?php echo $resumo['erros']; ?></strong>
                <small>respostas incorretas</small>
            </article>

            <article class="perfil-stat">
                <span>Aproveitamento</span>
                <strong><?php echo $resumo['aproveitamento']; ?>%</strong>
                <small>acertos ÷ questões</small>
            </article>

            <article class="perfil-stat">
                <span>Pontuação acumulada</span>
                <strong><?php echo $resumo['pontuacao']; ?></strong>
                <small>1 ponto por acerto</small>
            </article>

            <article class="perfil-stat">
                <span>Simulados concluídos</span>
                <strong><?php echo $resumo['simulados']; ?></strong>
                <small>histórico preservado</small>
            </article>
        </div>

        <?php if ($resumo['simulados'] === 0): ?>

            <div class="panel perfil-vazio">
                <h2>Ainda não há resultados</h2>
                <p>
                    Responda o simulado de questões do ENEM. Ao enviar, a correção
                    aparece na hora e o resultado é salvo neste perfil.
                </p>
                <a class="btn btn-primary" href="questoes.php">Começar o simulado</a>
            </div>

        <?php else: ?>

            <div class="perfil-colunas">

                <!-- Evolução dos últimos simulados -->
                <section class="panel">
                    <h2>Evolução</h2>
                    <p class="muted">Aproveitamento dos últimos <?php echo count($evolucao); ?> simulado<?php echo count($evolucao) === 1 ? '' : 's'; ?>.</p>

                    <ul class="evolucao">
                        <?php foreach ($evolucao as $atividade): ?>
                            <li>
                                <span class="evolucao-data"><?php echo date('d/m', strtotime($atividade['criado_em'])); ?></span>
                                <progress class="barra-progresso" value="<?php echo (float) $atividade['percentual']; ?>" max="100">
                                    <?php echo (int) $atividade['percentual']; ?>%
                                </progress>
                                <strong><?php echo (int) $atividade['percentual']; ?>%</strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>

                <!-- Desempenho por área das questões -->
                <section class="panel">
                    <h2>Desempenho por área</h2>
                    <p class="muted">Onde você acerta mais e onde vale reforçar a revisão.</p>

                    <ul class="areas">
                        <?php foreach ($areas as $area): ?>
                            <li>
                                <div class="areas-topo">
                                    <span><?php echo escaparHtml($area['area']); ?></span>
                                    <strong><?php echo $area['acertos']; ?>/<?php echo $area['total']; ?></strong>
                                </div>
                                <progress class="barra-progresso" value="<?php echo $area['percentual']; ?>" max="100">
                                    <?php echo $area['percentual']; ?>%
                                </progress>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>

            </div>

            <!-- Histórico completo -->
            <section class="panel perfil-historico">
                <div class="perfil-historico-topo">
                    <div>
                        <h2>Histórico de atividades</h2>
                        <p class="muted">Cada linha é um simulado concluído e salvo no banco.</p>
                    </div>
                    <a class="btn btn-ghost btn-sm" href="questoes.php">Novo simulado</a>
                </div>

                <div class="tabela-rolagem">
                    <table class="perfil-tabela">
                        <thead>
                            <tr>
                                <th scope="col">Data</th>
                                <th scope="col">Atividade</th>
                                <th scope="col">Acertos</th>
                                <th scope="col">Erros</th>
                                <th scope="col">Aproveitamento</th>
                                <th scope="col">Pontos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historico as $atividade): ?>
                                <?php $percentual = (int) $atividade['percentual']; ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($atividade['criado_em'])); ?></td>
                                    <td>Simulado ENEM</td>
                                    <td><?php echo (int) $atividade['acertos']; ?> de <?php echo (int) $atividade['total_questoes']; ?></td>
                                    <td><?php echo (int) $atividade['erros']; ?></td>
                                    <td>
                                        <span class="chip <?php echo $percentual >= 70 ? 'status-ok' : ($percentual < 50 ? 'status-erro' : ''); ?>">
                                            <?php echo $percentual; ?>%
                                        </span>
                                    </td>
                                    <td><?php echo (int) $atividade['pontuacao']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        <?php endif; ?>

    </div>
</section>

<?php require_once 'rodape.php'; ?>
