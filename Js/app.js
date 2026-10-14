//Registro
const abrir = document.getElementById("abrirRegistro");
const modal = document.getElementById("modalRegistro");
const cerrar = document.getElementById("cerrarRegistro");

abrir.addEventListener("click", function(){
    modal.style.display = "flex";
});

cerrar.addEventListener("click", function(){
    modal.style.display = "none";
});

window.addEventListener("click", function(e){
    if(e.target == modal){
        modal.style.display = "none";
    }
});

//Inicio de sesion
const abrirLogin = document.getElementById("abrirLogin");
const modalLogin = document.getElementById("modalLogin");
const cerrarLogin = document.getElementById("cerrarLogin");

abrirLogin.addEventListener("click", function() {
    modalLogin.style.display = "flex";
});

cerrarLogin.addEventListener("click", function() {
    modalLogin.style.display = "none";
});

window.addEventListener("click", function(e) {
    if (e.target == modalLogin) {
        modalLogin.style.display = "none";
    }
});

const botones = document.querySelectorAll(".btn-flip");
botones.forEach(boton =>{
    boton.addEventListener("click", function(){
        const card = this.closest(".card");
        card.classList.toggle("flip");
    });
});

document.getElementById("explorar").addEventListener("click", function(){
    document.getElementById("habitaciones-destacadas").scrollIntoView({
        behavior: "smooth"
    });
});

document.getElementById("app").addEventListener("click", function(){
    alert("La descarga de la App estará disponible pronto");
});
