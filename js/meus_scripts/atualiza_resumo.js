function atualiza_resumo() {

    var somaE = 0, somaMB = 0, somaB = 0, somaR = 0, somaI = 0;
    var resultadoE = 0, resultadoMB = 0, resultadoB = 0, resultadoR = 0, resultadoI = 0;
    var conceitoFinal = 0;
    var produtividade = [], lideranca = [], decisao = [], interpessoal = [], saude = [], planejamento = [], disciplina = [], disposicao = [], assiduidade = [], preparo = [];

    var listaProdutividade = document.getElementsByName("produtividade");
    var listaLideranca = document.getElementsByName("lideranca");
    var listaDecisao = document.getElementsByName("decisao");
    var listaInterpessoal = document.getElementsByName("interpessoal");
    var listaSaude = document.getElementsByName("saude");
    var listaPlanejamento = document.getElementsByName("planejamento");
    var listaDisciplina = document.getElementsByName("disciplina");
    var listaDisposicao = document.getElementsByName("disposicao");
    var listaAssiduidade = document.getElementsByName("assiduidade");
    var listaPreparo = document.getElementsByName("preparo");


    if (typeof listaProdutividade !== 'undefined') {
        for (var i = 0; i < listaProdutividade.length; ++i) {
            if (listaProdutividade[i].checked) {
                produtividade = listaProdutividade[i].value;
                break;
            }
        }
        switch (produtividade[0]) {
            case 'e':
                somaE += 1;
                break;
            case 'm':
                somaMB += 1;
                break;
            case 'b':
                somaB += 1;
                break;
            case 'r':
                somaR += 1;
                break;
            case 'i':
                somaI += 1;
                break;
        }
    }
    if (typeof listaLideranca !== 'undefined') {
        for (var i = 0; i < listaLideranca.length; ++i) {
            if (listaLideranca[i].checked) {
                lideranca = listaLideranca[i].value;
                break;
            }
        }
        switch (lideranca[0]) {
            case 'e':
                somaE += 1;
                break;
            case 'm':
                somaMB += 1;
                break;
            case 'b':
                somaB += 1;
                break;
            case 'r':
                somaR += 1;
                break;
            case 'i':
                somaI += 1;
                break;
        }
    }

    if (typeof listaDecisao !== 'undefined') {
        for (var i = 0; i < listaDecisao.length; ++i) {
            if (listaDecisao[i].checked) {
                decisao = listaDecisao[i].value;
                break;
            }
        }
        switch (decisao[0]) {
            case 'e':
                somaE += 1;
                break;
            case 'm':
                somaMB += 1;
                break;
            case 'b':
                somaB += 1;
                break;
            case 'r':
                somaR += 1;
                break;
            case 'i':
                somaI += 1;
                break;
        }
    }

    if (typeof listaInterpessoal !== 'undefined') {
        for (var i = 0; i < listaInterpessoal.length; ++i) {
            if (listaInterpessoal[i].checked) {
                interpessoal = listaInterpessoal[i].value;
                break;
            }
        }
        switch (interpessoal[0]) {
            case 'e':
                somaE += 1;
                break;
            case 'm':
                somaMB += 1;
                break;
            case 'b':
                somaB += 1;
                break;
            case 'r':
                somaR += 1;
                break;
            case 'i':
                somaI += 1;
                break;
        }
    }

    if (typeof listaSaude !== 'undefined') {
        for (var i = 0; i < listaSaude.length; ++i) {
            if (listaSaude[i].checked) {
                saude = listaSaude[i].value;
                break;
            }
        }
        switch (saude[0]) {
            case 'e':
                somaE += 1;
                break;
            case 'm':
                somaMB += 1;
                break;
            case 'b':
                somaB += 1;
                break;
            case 'r':
                somaR += 1;
                break;
            case 'i':
                somaI += 1;
                break;
        }
    }

    if (typeof listaPlanejamento !== 'undefined') {
        for (var i = 0; i < listaPlanejamento.length; ++i) {
            if (listaPlanejamento[i].checked) {
                planejamento = listaPlanejamento[i].value;
                break;
            }
        }
        switch (planejamento[0]) {
            case 'e':
                somaE += 1;
                break;
            case 'm':
                somaMB += 1;
                break;
            case 'b':
                somaB += 1;
                break;
            case 'r':
                somaR += 1;
                break;
            case 'i':
                somaI += 1;
                break;
        }
    }

    if (typeof listaDisciplina !== 'undefined') {
        for (var i = 0; i < listaDisciplina.length; ++i) {
            if (listaDisciplina[i].checked) {
                disciplina = listaDisciplina[i].value;
                break;
            }
        }
        switch (disciplina[0]) {
            case 'e':
                somaE += 1;
                break;
            case 'm':
                somaMB += 1;
                break;
            case 'b':
                somaB += 1;
                break;
            case 'r':
                somaR += 1;
                break;
            case 'i':
                somaI += 1;
                break;
        }
    }

    if (typeof listaDisposicao !== 'undefined') {
        for (var i = 0; i < listaDisposicao.length; ++i) {
            if (listaDisposicao[i].checked) {
                disposicao = listaDisposicao[i].value;
                break;
            }
        }
        switch (disposicao[0]) {
            case 'e':
                somaE += 1;
                break;
            case 'm':
                somaMB += 1;
                break;
            case 'b':
                somaB += 1;
                break;
            case 'r':
                somaR += 1;
                break;
            case 'i':
                somaI += 1;
                break;
        }
    }

    if (typeof listaAssiduidade !== 'undefined') {
        for (var i = 0; i < listaAssiduidade.length; ++i) {
            if (listaAssiduidade[i].checked) {
                assiduidade = listaAssiduidade[i].value;
                break;
            }
        }
        switch (assiduidade[0]) {
            case 'e':
                somaE += 1;
                break;
            case 'm':
                somaMB += 1;
                break;
            case 'b':
                somaB += 1;
                break;
            case 'r':
                somaR += 1;
                break;
            case 'i':
                somaI += 1;
                break;
        }
    }

    if (typeof listaPreparo !== 'undefined') {
        for (var i = 0; i < listaPreparo.length; ++i) {
            if (listaPreparo[i].checked) {
                preparo = listaPreparo[i].value;
                break;
            }
        }
        switch (preparo[0]) {
            case 'e':
                somaE += 1;
                break;
            case 'm':
                somaMB += 1;
                break;
            case 'b':
                somaB += 1;
                break;
            case 'r':
                somaR += 1;
                break;
            case 'i':
                somaI += 1;
                break;
        }
    }

    //Bloco abaixo somente imprime no console do navegador a quantidade de quesitos marcados
    /*     console.log('Excelente: ' + somaE)
        console.log('Muito bom: ' + somaMB)
        console.log('Bom: ' + somaB)
        console.log('Regular: ' + somaR)
        console.log('Insuficiente: ' + somaI) */

    //altera dinamicamente o RESUMO DA FICHA DE AVALIAÇÃO nos quesitos marcados
    document.getElementById('qtdeExcelente').textContent = somaE;
    document.getElementById('qtdeMuitoBom').textContent = somaMB;
    document.getElementById('qtdeBom').textContent = somaB;
    document.getElementById('qtdeRegular').textContent = somaR;
    document.getElementById('qtdeInsuficiente').textContent = somaI;

    resultadoE = somaE * 6
    resultadoMB = somaMB * 5
    resultadoB = somaB * 4
    resultadoR = somaR * 3
    resultadoI = somaI * 1

    document.getElementById('resultadoExcelente').textContent = resultadoE;
    document.getElementById('resultadoMuitoBom').textContent = resultadoMB;
    document.getElementById('resultadoBom').textContent = resultadoB;
    document.getElementById('resultadoRegular').textContent = resultadoR;
    document.getElementById('resultadoInsuficiente').textContent = resultadoI;

    conceitoFinal = (resultadoE + resultadoMB + resultadoB + resultadoR + resultadoI) / 10

    document.getElementById('conceitoFinal').textContent = conceitoFinal;

    var auxiliar = (conceitoFinal * 10) / 6

    var escalaDeZeroAdez = `Nota ${auxiliar.toFixed(2)} numa escala de 0 a 10`
    document.getElementById('escalaDeZeroAdez').textContent = escalaDeZeroAdez;

    document.getElementById("nota").value = conceitoFinal

    var teste = document.getElementById("nota").value
    console.log(teste);
}