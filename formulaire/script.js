
//redirection after mail
const form = document.getElementById('contact-form');

form.addEventListener('submit', async (e) => {
    e.preventDefault(); // Empêche l'envoi classique

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
            window.location.href = "https://www.canva.com/design/DAGkHyepW1w/bZeR3AoqZLTk09F7h-HMjg/edit?utm_content=DAGkHyepW1w&utm_campaign=designshare&utm_medium=link2&utm_source=sharebutton"; // Redirige après succès
        } else {
            alert("Une erreur est survenue. Merci de réessayer.");
        }
    } catch (error) {
        alert("Erreur de connexion. Vérifiez votre réseau.");
    }
});