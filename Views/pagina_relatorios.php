<?php
require_once '../Controllers/nivel_gestor.php';
include_once './header2.php';
?>

<div class="container">

    <div class="clearfix"></div>

    <div class="col-md-12">
        <H3>
            Relatórios disponíveis
        </H3>
        <div class="clearfix"><br></div>
    </div>

    <div class="col-md-12">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <h4 class="card-header">Relatórios de promoções</h4>
                    <div class="card-body">
                        <h5 class="card-title">Promoções passadas</h5>
                        <p class="card-text">Selecione o militar e confira os <strong>registros passados</strong> de promoção</strong>.</p>
                        <a href="listar_militares_relatorio_de_promocoes.php" class="btn btn-primary">Selecionar</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <h4 class="card-header">Documentos entregues</h4>
                    <div class="card-body">
                        <h5 class="card-title">Militares constantes no LQ</h5>
                        <p class="card-text">Relatório de documentação entregue à Secretaria das Comissões de Promoção (SCP)</strong>.</p>
                        <a href="pesquisa_docs_entregues.php" class="btn btn-primary">Selecionar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <h4 class="card-header">Documentos entregues (TC BM)</h4>
                    <div class="card-body">
                        <h5 class="card-title">Específico para tenentes-coronéis</h5>
                        <p class="card-text">Relatório de documentação entregue à Secretaria das Comissões de Promoção (SCP)</strong>.</p>
                        <a href="pesquisa_docs_entregues_TC.php" class="btn btn-primary">Selecionar</a>
                    </div>
                </div>
            </div>

        </div>

        <div class="clearfix"></br></div>

        <!-- SEGUNDA LINHA DE OPÇÕES -->

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <h4 class="card-header">Certidões positivadas</h4>
                    <div class="card-body">
                        <h5 class="card-title">Listagem de certidões positivadas</h5>
                        <p class="card-text">Selecione o período em questão para exibir os resultados pertinentes</strong>.</p>
                        <a href="pesquisa_positivados.php" class="btn btn-primary">Selecionar</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <h4 class="card-header">Afastamentos</h4>
                    <div class="card-body">
                        <h5 class="card-title">Militares com afastamentos</h5>
                        <p class="card-text">Relatório de afastamentos que interferem no cômputo de interstício e de tempo arregimentado.</strong>.</p>
                        <a href="../Views/lista_tempo_nao_arregimentado.php" class="btn btn-primary">Selecionar</a>
                    </div>
                </div>
            </div>
            <!-- 
            <div class="col-md-4">
                <div class="card">
                    <h4 class="card-header">Documentos entregues (TC BM)</h4>
                    <div class="card-body">
                        <h5 class="card-title">Específico para tenentes-coronéis</h5>
                        <p class="card-text">Relatório de documentação entregue à Secretaria das Comissões de Promoção (SCP)</strong>.</p>
                        <a href="pesquisa_docs_entregues_TC.php" class="btn btn-primary">Selecionar</a>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
</div>


<?php
include_once './footer.php';
?>