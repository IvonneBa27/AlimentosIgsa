//Copiar texto
document.addEventListener("copy", function (event) {
    event.clipboardData.setData("text/plain", "No se permite copiar en esta página web");
    event.preventDefault();
});


//Deshabilitar el menú contextual (clic derecho)
document.addEventListener("contextmenu", function (event) {
    event.preventDefault();
});