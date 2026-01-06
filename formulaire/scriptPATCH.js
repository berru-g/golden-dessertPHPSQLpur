// Correction de l'ID du formulaire
const form = document.getElementById('contactForm'); // Pas 'contact-form'

// Anti-bot : vérification invisible
let formStartTime = Date.now();
let mouseMovements = 0;
let keyStrokes = 0;

// Détecte l'activité humaine
document.addEventListener('mousemove', () => mouseMovements++);
document.addEventListener('keydown', () => keyStrokes++);

// Redirection après mail
if (form) {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Calcul du temps passé sur le formulaire
        const timeSpent = (Date.now() - formStartTime) / 1000;
        
        // Vérification activité humaine minimale
        if (timeSpent < 3 && mouseMovements < 2 && keyStrokes < 5) {
            // Comportement suspect - pourrait être un bot
            console.warn('Activité suspecte détectée');
            // Mais on laisse passer pour ne pas pénaliser les vrais utilisateurs rapides
        }
        
        // Validation des champs côté client (doublon de sécurité)
        const email = document.getElementById('email').value;
        if (!email.includes('@') || !email.includes('.')) {
            alert('Veuillez saisir une adresse email valide.');
            return;
        }
        
        const message = document.getElementById('message').value;
        if (message.trim().length < 10) {
            alert('Le message doit contenir au moins 10 caractères.');
            return;
        }

        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method: form.method,
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                // Redirige vers le catalogue Canva
                window.location.href = "https://www.canva.com/design/DAG1GrWt6cU/w9X8Y9wY2XRE6S6mDIW39w/edit?utm_content=DAG1GrWt6cU&utm_campaign=designshare&utm_medium=link2&utm_source=sharebutton";
            } else {
                const errorText = await response.text();
                if (errorText.includes('patienter')) {
                    alert("Vous avez soumis trop de demandes. Veuillez patienter quelques instants.");
                } else {
                    alert("Une erreur est survenue. Merci de réessayer.");
                }
            }
        } catch (error) {
            alert("Erreur de connexion. Vérifiez votre réseau.");
        }
    });
}