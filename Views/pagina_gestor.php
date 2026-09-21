<?php
require_once '../Controllers/nivel_gestor.php';
include_once './header2.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <nav class="navbar">
                <form class="container-fluid justify-content-start">
                    <a class="btn btn-primary me-2" href="pagina_ferramentas.php">Ferramentas</a>
                    <a class="btn btn-primary me-2" href="pagina_relatorios.php">Relatórios</a>
                    <a class="btn btn-primary me-2" href="pagina_gestao.php">Gestão</a>
                </form>
            </nav>
            <hr>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mx-auto">
            <h3></h3><br>
            <h4>Pesquisar militar</h4>
            <div class="form-row">
                <div class="form-group">
                    <form method="GET" action="../Views/listar_militares.php">
                        <div class="input-group mb-3">
                            <span class="input-group-text">Nome</span>
                            <input type="text" class="form-control" placeholder="Digite o nome ou parte dele" name="pesquisar">
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">Posto/grad</span>
                            <select class="form-select" name="posto_grad">
                                <option selected disabled>Selecione o posto ou graduação</option>
                                <option value="CEL BM">Coronel</option>
                                <option value="TC BM">Tenente Coronel</option>
                                <option value="MAJ BM">Major</option>
                                <option value="CAP BM">Capitão</option>
                                <option value="1º TEN BM">1º Tenente</option>
                                <option value="2º TEN BM">2º Tenente</option>
                                <option value="ASP OF BM">Aspirante-a-oficial</option>
                                <option value="CAD BM">Cadete</option>
                                <option value="AL OF BM">Aluno oficial CAOC</option>
                                <option value="ST BM">Sub-tentente</option>
                                <option value="1º SGT BM">1º Sargento</option>
                                <option value="2º SGT BM">2º Sargento</option>
                                <option value="3º SGT BM">3º Sargento</option>
                                <option value="CB BM">Cabo</option>
                                <option value="SD BM">Soldado</option>
                                <option value="AL SD BM">Aluno Soldado</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Pesquisar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<?php
include_once './footer.php';
?>