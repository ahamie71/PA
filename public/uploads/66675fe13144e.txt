document.getElementById('registrationForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const nom = document.getElementById('nom').value;
    const prenom = document.getElementById('prenom').value;
    const telephone = document.getElementById('telephone').value;
    const email = document.getElementById('email').value;
    const formation = document.getElementById('formation').value;
    const accepte = document.getElementById('accepte').checked;

    const options1 = Array.from(document.querySelectorAll('input[name="options1[]"]:checked')).map(el => el.value);
    const options2 = Array.from(document.querySelectorAll('input[name="options2[]"]:checked')).map(el => el.value);

    if (accepte) {
        try {
            const newData = {
                nom,
                prenom,
                telephone,
                email,
                formation,
                options1,
                options2
            };

            const response = await fetch('http://localhost:8001/saveData.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(newData)
            });

            const responseData = await response.json();
        
            document.getElementById('registrationForm').reset();
        } catch (error) {
            console.error('Erreur lors de la soumission des données:', error);
        }
    } else {
        console.log('Veuillez accepter la conservation et l\'utilisation de vos données.');
    }
});
