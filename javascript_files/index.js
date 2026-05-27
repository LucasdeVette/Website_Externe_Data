console.log("Mijn naam is Lucas");
console.log("Ik leer javascript");
let naam = "Lucas";
let leeftijd = 19;
console.log(`mijn naam is ${naam} en ik ben ${leeftijd} jaar oud`);
console.log (`test ${1 + 1}`);
if (leeftijd >= 18) {
    console.log ("je bent volwassen");
} else {
    console.log("je bent minderjarig");
}

let wachtwoord = "hallo123";
if (wachtwoord === "hallo123") {
    console.log ("wachtwoord is correct");
} else {
    console.log ("wachtwoord is incorrect");
}

let darkmode = document.getElementById("darkmode");
let darkmodeicon = document.getElementById("darkmodeicon");
let profilebutton = document.getElementById("profilebutton");
let dropdownmenu = document.getElementById("dropdownmenu");

darkmode.addEventListener("click", function() {
    document.body.classList.toggle("darkmodestyle");

    if (document.body.classList.contains("darkmodestyle")) {
        darkmodeicon.src = "../images/lightmode.png.svg";
    } else {
        darkmodeicon.src = "../images/darkmode.png.svg";
    }


});

profilebutton.addEventListener("click", function() {

    dropdownmenu.classList.toggle("show");
});




