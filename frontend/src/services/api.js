const API_URL = "http://localhost/menumaster/backend/controllers/UsuarioController.php";

export const registrarUsuario = async (usuario) => {
    try {
        const response = await fetch(API_URL, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(usuario),
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || "Error desconocido");
        }

        return data; // Devuelve el mensaje de éxito o error
    } catch (error) {
        return { error: error.message };
    }
};