document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("loginForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(form);

        try {
            const response = await fetch("api/login.php", {
                method: "POST",
                body: formData,
                headers: { "Accept": "application/json" }, // fuerza respuesta JSON limpia
            });

            const raw = await response.text();
            console.log("RAW RESPONSE:", raw);

            let result;
            try {
                result = JSON.parse(raw);
            } catch (err) {
                console.error("❌ Error parseando JSON:", raw);
                alert("Error del servidor: respuesta inválida.");
                return;
            }

            if (result.success) {
                // login correcto
                window.location.href = "dashboard.php";
            } else {
                alert("❌ " + (result.message || "Error desconocido"));
            }

        } catch (error) {
            console.error("❌ Error en fetch:", error);
            alert("No se pudo conectar al servidor.");
        }
    });
});
