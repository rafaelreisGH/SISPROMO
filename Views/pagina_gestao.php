<?php
require_once '../Controllers/nivel_gestor.php';
include_once './header2.php';
?>

<div class="container">

    <div class="clearfix"></div>

    <div class="col-md-12">
        <h3>
            Gestão da Secretaria das Comissões de Promoção
        </h3>
        <div class="clearfix"><br></div>
    </div>

    <div class="col-md-12">

        <!-- Linha 1 -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <h5 class="card-header">Vagas por quadro</h5>
                    <div class="card-body">
                        <h6 class="card-title">Conforme LC Nº 530/14</h6>
                        <p class="card-text">Realize a edição das vagas conforme legislação.</p>
                        <a href="gestao_de_vagas.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <h5 class="card-header">Conferência de Vagas</h5>
                    <div class="card-body">
                        <h6 class="card-title">Conforme LC Nº 530/14</h6>
                        <p class="card-text">Confira as vagas previstas / ocupadas.</p>
                        <a href="painel_vagas.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <h5 class="card-header">Inativação / Excedente</h5>
                    <div class="card-body">
                        <h6 class="card-title">Situação e Atos Administrativos</h6>
                        <p class="card-text">Controle de status, excedentes e publicações de atos.</p>
                        <a href="inativar_militares.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <!-- Linha 2 -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <h5 class="card-header">Reunião da <strong>CPO</strong></h5>
                    <div class="card-body">
                        <h6 class="card-title">Comissão de Promoção de Oficiais</h6>
                        <p class="card-text">Realize a gestão das reuniões da comissão.</p>
                        <a href="gestao_reunioes.php?comissao=CPO" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <h5 class="card-header">Reunião da <strong>CPP</strong></h5>
                    <div class="card-body">
                        <h6 class="card-title">Comissão de Promoção de Praças</h6>
                        <p class="card-text">Realize a gestão das reuniões da comissão.</p>
                        <a href="gestao_reunioes.php?comissao=CPP" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>
        </div>
        <hr>

        <!-- Linha 3 -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <h5 class="card-header">Relatório</h5>
                    <div class="card-body">
                        <h6 class="card-title">Corregedoria</h6>
                        <p class="card-text">Cadastre e gerencie os relatórios da corregedoria por ano e semestre.</p>
                        <a href="gestao_relatorio_corregedoria.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <h5 class="card-header">Processo Promocional</h5>
                    <div class="card-body">
                        <h6 class="card-title">Oficiais e Praças</h6>
                        <p class="card-text">Gerencie os ciclos de promoção, calcule o LQ e acompanhe as fases dos processos.</p>
                        <a href="gestao_processo_promocional.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"><br></div>

    </div>
</div>

<?php
include_once './footer.php';
?>