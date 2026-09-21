<?php
require_once '../Controllers/nivel_gestor.php';
include_once './header2.php';
?>

<div class="container">

    <div class="clearfix"></div>

    <div class="col-md-12">
        <H3>
            Ações disponíveis
        </H3>
        <div class="clearfix"><br></div>
    </div>

    <div class="col-md-12">
        <div class="row">
            <h4>
                Listagem de candidatos à promoção
            </h4>
            <div class="col-md-4">
                <div class="card">
                    <h5 class="card-header">Limite Quantitativo</h5>
                    <div class="card-body">
                        <h6 class="card-title">Militares em geral</h6>
                        <p class="card-text">Documento destinado a exibir os militares que possuem ou possuirão interstício mínimo para a próxima promoção.</p>
                        <a href="selecao_criterios_de_pesquisa_LQ.php" class="btn btn-primary">Selecionar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <h5 class="card-header">Interstício para último posto</h5>
                    <div class="card-body">
                        <h6 class="card-title">Específico para Tenentes Coronéis</h6>
                        <p class="card-text">Documento destinado a exibir os Tenentes Coronéis que possuem ou possuirão interstício mínimo para promoção (por merecimento).</p>
                        <a href="selecao_criterios_de_pesquisa_LQ_TC.php" class="btn btn-primary">Selecionar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <!-- <div class="card">
                    <h5 class="card-header">Teste cômputo de vagas</h5>
                    <div class="card-body">
                        <h6 class="card-title">#</h6>
                        <p class="card-text">#</p>
                        <a href="teste_vagas.php" class="btn btn-primary">Selecionar</a>
                    </div>
                </div> -->
            </div>
        </div>
        <hr>

        <div class="clearfix"></br></div>

        <div class="row">
            <h4>
                Processamento de promoção
            </h4>
            <div class="col-md-4">
                <div class="card">
                    <h5 class="card-header">Processar Promoção (1)</h5>
                    <div class="card-body">
                        <h6 class="card-title">Lista geral, não filtrada.</h6>
                        <p class="card-text">Esta função atualiza em lote o posto/graduação bem como as respectivas antiguidades.</p>
                        <a href="selecao_criterios_de_pesquisa.php?promover=1" class="btn btn-primary">Selecionar</a>
                    </div>
                </div>
            </div>
            <!--
            <div class="col-md-4">
                <div class="card">
                    <h5 class="card-header">Processar Promoção (2)</h5>
                    <div class="card-body">
                        <h6 class="card-title">Filtrada por posto/grad. e quadro</h6>
                        <p class="card-text">Esta função atualiza em lote o posto/graduação bem como as respectivas antiguidades.</p>
                        <a href="selecao_criterios_de_pesquisa_LQ_promocao.php" class="btn btn-primary">Selecionar</a>
                    </div>
                </div>
            </div>
             <div class="col-md-4">
                <div class="card">
                    <h5 class="card-header">Cadastro de AIS</h5>
                    <div class="card-body">
                        <h6 class="card-title">Ata de inspeção de saúde</h6>
                        <p class="card-text">Registre os dados relacionados à realização de inspeção de saúde.</p>
                        <a href="selecao_militar.php" class="btn btn-primary">Selecionar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <h5 class="card-header">Cadastro de FAD</h5>
                    <div class="card-body">
                        <h6 class="card-title">Inserção de FAD</h6>
                        <p class="card-text">Registre os dados relacionados à ficha de avaliação de desempenho.</p>
                        <a href="selecao_militar_fad.php" class="btn btn-primary">Selecionar</a>
                    </div>
                </div>
            </div> -->

        </div>
        <hr>

        <div class="clearfix"></br></div>

        <div class="row">
            <h4>
                Opções gerais de alteração de cadastro
            </h4>
            <div class="col-md-4">
                <div class="card">
                    <h5 class="card-header">Cadastro de militar</h5>
                    <div class="card-body">
                        <h6 class="card-title">Registro de informações básicas</h6>
                        <p class="card-text">Informe os dados necessários e cadastre os militares no banco de dados.</p>
                        <a href="cadastrar_militar.php" class="btn btn-primary">Selecionar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <h5 class="card-header">Atualização de antiguidade</h5>
                    <div class="card-body">
                        <h6 class="card-title">Atualização individual</h6>
                        <p class="card-text">Informe a posição que o militar deve ocupar na escala hieráquica. <strong>Não altera posto/graduação.</strong><br></p>
                        <a href="selecao_criterios_de_pesquisa.php?alteracao_antiguidade=1" class="btn btn-primary">Selecionar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <h5 class="card-header">Registro de promoções passadas</h5>
                    <div class="card-body">
                        <h6 class="card-title">Atualização em lote</h6>
                        <p class="card-text">Selecione os militares e atualize em lote os <strong>registros passados</strong> de promoção. Esta opção <strong> não interfere na antiguidade, nem altera posto/graduação</strong>.</p>
                        <a href="selecao_criterios_de_pesquisa.php" class="btn btn-primary">Selecionar</a>
                    </div>
                </div>
            </div>

        </div>

        <div class="clearfix"></br></div>

    </div>
</div>


<?php
include_once './footer.php';
?>