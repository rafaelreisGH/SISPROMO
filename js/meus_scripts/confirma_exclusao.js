function confirma_exclusao() {
    //pede ao usuário confirmação
    var resposta = confirm("Deseja realmente remover esse registro?\nSerão apagados todas as informações, inclusive o arquivo digital caso tenha sido encaminhado.");
     if (resposta == false) {
        return false;  
     }
}