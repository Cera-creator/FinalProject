const didYouKnowBox = document.getElementById('didYouKnow');

const facts = [
    "Did you know? The first widely available and influential computer game is credited as 'Spacewar!'. ",
    "Did you know? There are over 2,000 video game design schools worldwide. ",
    "Did you know? The average salary for a video game designer is around $85,000. ",
    "Did you know? In early development, Batman: Arkham Asylum was originally going to be a rhythmic action game.",
    "Did you know? The NEW Most Expensive Game In History is 'Star Citizen' with a $590 Million Price Tag for Development.",
    "Did you know? A Game Boy Survived a Bombing During the Gulf War.",
    "Did you know? 'World of Warcraft' Has Its Own Language Course."
];

let currentFactIndex = 0;

function updateFact() {
    didYouKnowBox.style.opacity = 0;
    setTimeout(() => {
        didYouKnowBox.textContent = facts[currentFactIndex];
        currentFactIndex = (currentFactIndex + 1) % facts.length; 
        didYouKnowBox.style.opacity = 1;
    }, 500); 
}

updateFact();

setInterval(updateFact, 10000);
