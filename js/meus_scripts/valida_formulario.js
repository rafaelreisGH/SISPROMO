function validateForm() {

  //verifica se cada um dos radios foram preenchidos
  let a = document.forms["myForm"]["produtividade"].value;
  if (a == "") {
    alert("O quesito PRODUTIVIDADE deve ser preenchido");
    return false;
  }
  let b = document.forms["myForm"]["lideranca"].value;
  if (b == "") {
    alert("O quesito LIDERANÇA deve ser preenchido");
    return false;
  }
  let c = document.forms["myForm"]["decisao"].value;
  if (c == "") {
    alert("O quesito DECISÃO deve ser preenchido");
    return false;
  }
  let d = document.forms["myForm"]["interpessoal"].value;
  if (d == "") {
    alert("O quesito RELACIONAMENTO INTERPESSOAL deve ser preenchido");
    return false;
  }
  let e = document.forms["myForm"]["saude"].value;
  if (e == "") {
    alert("O quesito SAÚDE FÍSICA deve ser preenchido");
    return false;
  }
  let f = document.forms["myForm"]["planejamento"].value;
  if (f == "") {
    alert("O quesito PLANEJAMENTO deve ser preenchido");
    return false;
  }
  let g = document.forms["myForm"]["disciplina"].value;
  if (g == "") {
    alert("O quesito DISCIPLINA deve ser preenchido");
    return false;
  }
  let h = document.forms["myForm"]["disposicao"].value;
  if (h == "") {
    alert("O quesito DISPOSIÇÃO PARA O TRABALHO deve ser preenchido");
    return false;
  }
  let i = document.forms["myForm"]["assiduidade"].value;
  if (i == "") {
    alert("O quesito ASSIDUIDADE deve ser preenchido");
    return false;
  }
  let j = document.forms["myForm"]["preparo"].value;
  if (j == "") {
    alert("O quesito PREPARO INTELECTUAL deve ser preenchido");
    return false;
  }

  //verifica se a justificativa foi preenchida quando a nota for menor que 3
  let k = document.forms["myForm"]["notaCalculada"].value;
  let l = document.forms["myForm"]["textoJustificativa"].value;
  if ((k < 3) && (l == "")) {
    alert("É obrigatório o preenchimento da justificativa quando o conceito final for menor que 3.");
    return false;
  }

}