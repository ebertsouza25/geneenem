<?php
// Banco de questões do ENEM. Este arquivo é só dados: as páginas questoes.php
// e resultado.php percorrem o array com foreach para montar os cards e a
// correção. Cada questão guarda o ano, a área, o título, o enunciado, as
// alternativas, o gabarito e a explicação.

$questoes = [

    1 => [
        'numero_original' => 106,
        'titulo' => 'Arroz dourado e vitamina A',
        'ano' => 'ENEM 2025',
        'area' => 'Biotecnologia',
        'enunciado' => 'Golden Rice, ou arroz dourado, é uma variedade de arroz enriquecida em betacaroteno, precursor da vitamina A. Foi desenvolvida para ajudar a combater a doença decorrente da deficiência dessa vitamina. Para a obtenção dessa variedade de arroz, foram utilizados métodos de engenharia genética.
BORÉM, A.; SANTOS, F. R. Entendendo a biotecnologia. Viçosa: UFV, 2008.
Esse alimento contribui para diminuir a carência associada a qual doença?',
        'alternativas' => [
            'A' => 'Hemofilia.',
            'B' => 'Escorbuto.',
            'C' => 'Raquitismo.',
            'D' => 'Cegueira noturna.',
            'E' => 'Anemia perniciosa.'
        ],
        'gabarito' => 'D',
        'explicacao' => 'O Golden Rice tem betacaroteno, que é precursor da vitamina A. A falta de vitamina A pode causar cegueira noturna.'
    ],

    2 => [
        'numero_original' => 93,
        'titulo' => 'Heredograma do daltonismo',
        'ano' => 'ENEM 2024',
        'area' => 'Hereditariedade',
        'enunciado' => 'Introdução à genética
Um dos exemplos mais conhecidos de herança recessiva ligada ao cromossomo X é o daltonismo. Como em qualquer distúrbio recessivo ligado ao cromossomo X, existem muito mais homens apresentando o fenótipo com esse tipo de daltonismo do que mulheres. Um casal formado por um homem não daltônico e por uma mulher gestante também não daltônica, mas portadora do gene recessivo para esse tipo de daltonismo, está esperando um bebê. Em uma das consultas de pré-natal, o casal recebeu um heredograma que contém todas as possibilidades de genótipo para esse bebê.
Qual heredograma foi recebido pelo casal?
Cada alternativa apresenta um heredograma, em que são descritos os filhos do casal composto pelo pai não daltônico e pela mãe heterozigota, portadora do gene para daltonismo.',
        'alternativas' => [
            'A' => 'Mulher não daltônica; mulher daltônica; homem daltônico; e homem não daltônico.',
            'B' => 'Mulher não daltônica; mulher heterozigota, portadora do gene para daltonismo; homem não daltônico; e homem daltônico.',
            'C' => 'Duas mulheres heterozigotas, portadoras do gene para daltonismo; e dois homens não daltônicos.',
            'D' => 'Duas mulheres não daltônicas; e dois homens daltônicos.',
            'E' => 'Duas mulheres daltônicas; e dois homens não daltônicos.'
        ],
        'gabarito' => 'B',
        'explicacao' => 'O pai não daltônico passa o cromossomo X normal para as filhas e o Y para os filhos. A mãe portadora pode passar o X normal ou o X com o gene recessivo. Por isso podem nascer filhas normais ou portadoras, e filhos normais ou daltônicos.'
    ],

    3 => [
        'numero_original' => 112,
        'titulo' => 'RT-PCR e genes expressos',
        'ano' => 'ENEM 2024',
        'area' => 'DNA e RNA',
        'enunciado' => 'O desenvolvimento da biotecnologia e da clonagem gênica em procariotos fez com que a produção de proteínas se tornasse mais intensa, rápida e econômica. Para a produção de hormônios, enzimas e proteínas de resistência a drogas, uma variação da técnica de reação em cadeia pela polimerase (PCR, na sigla em inglês) utiliza a enzima transcriptase reversa (RT-PCR), que sintetiza moléculas de DNA complementares a partir de fitas de RNA.
Nesse contexto, essa técnica é importante para detectar genes',
        'alternativas' => [
            'A' => 'expressos.',
            'B' => 'plasmidiais.',
            'C' => 'bacterianos.',
            'D' => 'dominantes.',
            'E' => 'autossômicos.'
        ],
        'gabarito' => 'A',
        'explicacao' => 'A RT-PCR usa RNA como molde para formar DNA complementar. Assim, ela ajuda a detectar genes que estão sendo expressos.'
    ],

    4 => [
        'numero_original' => 121,
        'titulo' => 'HTLV-1 e linfócitos T',
        'ano' => 'ENEM 2024',
        'area' => 'Vírus e células',
        'enunciado' => 'O vírus linfotrópico de células T humanas tipo 1 (HTLV-1): quando suspeitar da infecção?
O vírus linfotrópico de células T humanas tipo 1 (HTLV-1) é um retrovírus do mesmo grupo do vírus da imunodeficiência humana (HIV). Ambos são transmitidos da mesma forma e infectam as mesmas células de defesa do organismo, os linfócitos T. A diferença entre eles é que o HTLV-1 estimula o aumento da produção desses linfócitos, enquanto o HIV causa destruição dessas células.
Uma possível consequência da infecção por HTLV-1 é o desenvolvimento de',
        'alternativas' => [
            'A' => 'aids.',
            'B' => 'câncer.',
            'C' => 'diabetes.',
            'D' => 'hepatite B.',
            'E' => 'hemorragia.'
        ],
        'gabarito' => 'B',
        'explicacao' => 'O HTLV-1 aumenta a produção de linfócitos T. Esse aumento descontrolado pode estar ligado ao desenvolvimento de câncer.'
    ],

    5 => [
        'numero_original' => 96,
        'titulo' => 'Micronúcleos e genotoxicidade',
        'ano' => 'ENEM 2023',
        'area' => 'Cromossomos',
        'enunciado' => 'Avaliação de substâncias genotóxicas
Micronúcleos em células tumorais: biologia e implicações para a tumorigênese
O ensaio de micronúcleos é um teste de avaliação de genotoxicidade que associa a presença de micronúcleos (pequenos núcleos que aparecem próximo aos núcleos das células) com lesões genéticas. Os micronúcleos são fragmentos de DNA encapsulados, provenientes do fuso mitótico durante a divisão celular.
Os micronúcleos se originam dos(as)',
        'alternativas' => [
            'A' => 'nucléolos.',
            'B' => 'lisossomos.',
            'C' => 'ribossomos.',
            'D' => 'mitocôndrias.',
            'E' => 'cromossomos.'
        ],
        'gabarito' => 'E',
        'explicacao' => 'Micronúcleos são fragmentos de DNA encapsulados. Como o DNA fica nos cromossomos, a origem correta são os cromossomos.'
    ],

    6 => [
        'numero_original' => 99,
        'titulo' => 'Trissomia do 21 e gametas',
        'ano' => 'ENEM 2023',
        'area' => 'Síndrome de Down',
        'enunciado' => 'Pais com síndrome de Down
Três anos após dar à luz, mãe portadora de síndrome de Down revela detalhes de seu dia a dia
A síndrome de Down é uma alteração genética associada à trissomia do cromossomo 21, ou seja, o indivíduo possui três cromossomos 21 e não um par, como é normal. Isso ocorre pela união de um gameta contendo um cromossomo 21 com um gameta possuidor de dois cromossomos 21. Embora, normalmente, as mulheres com a síndrome sejam estéreis, em 2008, no interior de São Paulo, uma delas deu à luz uma menina sem a síndrome de Down.
Sabendo disso, um jovem casal, ambos com essa síndrome, procura um médico especialista para aconselhamento genético porque querem ter um bebê.
O médico informa ao casal que, com relação ao cromossomo 21, os zigotos formados serão',
        'alternativas' => [
            'A' => 'todos normais.',
            'B' => 'todos tetrassômicos.',
            'C' => 'apenas normais ou tetrassômicos.',
            'D' => 'apenas trissômicos ou tetrassômicos.',
            'E' => 'normais, trissômicos ou tetrassômicos.'
        ],
        'gabarito' => 'E',
        'explicacao' => 'Na síndrome de Down há trissomia do cromossomo 21. Quando dois indivíduos com a síndrome formam gametas, podem surgir zigotos normais, trissômicos ou tetrassômicos.'
    ],

    7 => [
        'numero_original' => 117,
        'titulo' => 'Vacina de RNA mensageiro',
        'ano' => 'ENEM 2023',
        'area' => 'RNA mensageiro',
        'enunciado' => 'A tecnologia de vacinas de RNA mensageiro (RNAm) é investigada há anos. Avanços científicos em genética molecular permitiram desenvolver uma vacina para controle da pandemia da covid-19 causada pelo vírus de RNA SARS-CoV-2. A vacina de RNAm tem sequências de genes do vírus. Entretanto, por ser muito instável, o RNAm deve ser recoberto por uma capa de lipídios que evita sua degradação e favorece sua ação. Dessa forma, o RNAm desempenhará sua função específica atuando no mesmo compartimento celular de sempre.
A imunização produzida por esse tipo de vacina é alcançada por meio da',
        'alternativas' => [
            'A' => 'estimulação de leucócitos induzida pela capa lipídica contendo RNAm.',
            'B' => 'atuação do RNAm como sequestrador do vírus para o meio extracelular.',
            'C' => 'tradução do RNAm em proteína viral, desencadeando a resposta antigênica.',
            'D' => 'competição entre o RNAm vacinal e o RNA viral pelos sítios dos ribossomos.',
            'E' => 'incorporação do RNAm viral ao genoma do hospedeiro, gerando novo fenótipo.'
        ],
        'gabarito' => 'C',
        'explicacao' => 'A vacina de RNAm funciona porque o RNA mensageiro é traduzido em proteína viral. Essa proteína ativa a resposta do sistema imune.'
    ],

    8 => [
        'numero_original' => 91,
        'titulo' => 'Trypanosoma cruzi e antígeno tumoral',
        'ano' => 'ENEM 2022',
        'area' => 'Genética e imunidade',
        'enunciado' => 'O protozoário Trypanosoma cruzi, causador da doença de Chagas, pode ser a nova arma da medicina contra o câncer. Pesquisadores brasileiros conseguiram criar uma vacina contra a doença usando uma variação do protozoário incapaz de desencadear a patologia (não patogênico). Para isso, realizaram uma modificação genética criando um T. cruzi capaz de produzir também moléculas fabricadas pelas células tumorais. Quando o organismo inicia o combate ao protozoário, entra em contato também com a molécula tumoral, que passa a ser vista também pelo sistema imune como um indicador de células do protozoário. Depois de induzidas as defesas, estas passam a destruir todas as células com a molécula tumoral, como se lutassem apenas contra o protozoário.
Qual o mecanismo utilizado no experimento para enganar as células de defesa, fazendo com que ataquem o tumor?',
        'alternativas' => [
            'A' => 'Autoimunidade.',
            'B' => 'Hipersensibilidade.',
            'C' => 'Ativação da resposta inata.',
            'D' => 'Apresentação de antígeno específico.',
            'E' => 'Desencadeamento de processo anti-inflamatório.'
        ],
        'gabarito' => 'D',
        'explicacao' => 'O protozoário modificado apresenta uma molécula tumoral ao sistema imune. Assim, as defesas passam a reconhecer esse antígeno específico.'
    ],

    9 => [
        'numero_original' => 107,
        'titulo' => 'Antimicrobianos e microtúbulos',
        'ano' => 'ENEM 2022',
        'area' => 'DNA, RNA e células',
        'enunciado' => 'Antimicrobianos são substâncias naturais ou sintéticas que têm capacidade de matar ou inibir o crescimento de microrganismos. A tabela apresenta uma lista de antimicrobianos hipotéticos, bem como suas ações e efeitos sobre o metabolismo microbiano.
Antimicrobiano 1. Ação: une-se aos ribossomos. Efeito: impede a síntese proteica.
Antimicrobiano 2. Ação: une-se aos microtúbulos. Efeito: impede a segregação das cromátides.
Antimicrobiano 3. Ação: une-se aos fosfolipídeos da membrana plasmática. Efeito: reduz a permeabilidade da membrana plasmática.
Antimicrobiano 4. Ação: interfere na síntese de timina. Efeito: inibe a síntese de DNA.
Antimicrobiano 5. Ação: interfere na síntese de uracila. Efeito: impede a síntese de RNA.
Qual dos antimicrobianos deve ser utilizado para curar uma infecção causada por um fungo sem afetar as bactérias da microbiota normal do organismo?',
        'alternativas' => [
            'A' => '1',
            'B' => '2',
            'C' => '3',
            'D' => '4',
            'E' => '5'
        ],
        'gabarito' => 'B',
        'explicacao' => 'Fungos são eucariontes e possuem microtúbulos envolvidos na divisão celular. Bactérias não possuem essa mesma estrutura, por isso o antimicrobiano 2 afeta fungos sem atingir a microbiota bacteriana.'
    ],

    10 => [
        'numero_original' => 130,
        'titulo' => 'Terapia gênica da retina',
        'ano' => 'ENEM 2022',
        'area' => 'Terapia gênica',
        'enunciado' => 'A Agência Nacional de Vigilância Sanitária (Anvisa) aprovou um produto de terapia gênica no país, indicado para o tratamento da distrofia hereditária da retina. O procedimento é recomendado para crianças acima de 12 meses e adultos com perda de visão causada pela mutação do gene humano RPE65. O produto, elaborado por engenharia genética, é composto por um vírus, no qual foi inserida uma cópia do gene normal humano RPE65 para corrigir o funcionamento das células da retina.
O sucesso dessa terapia advém do fato de que o produto favorecerá a',
        'alternativas' => [
            'A' => 'correção do código genético para a tradução da proteína.',
            'B' => 'alteração do RNA ribossômico ligado à síntese da proteína.',
            'C' => 'produção de mutações benéficas para a correção do problema.',
            'D' => 'liberação imediata da proteína normal na região ocular humana.',
            'E' => 'expressão do gene responsável pela produção da enzima normal.'
        ],
        'gabarito' => 'E',
        'explicacao' => 'A terapia gênica coloca uma cópia normal do gene RPE65 nas células da retina, permitindo a expressão do gene e a produção da enzima normal.'
    ]

];

// Total de questões do banco (usado no quiz e nos textos das páginas)
$total = count($questoes);
?>
