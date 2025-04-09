import { useState } from "react";
import { registrarUsuario } from "../services/api";

const Registro = () => {
    const [formData, setFormData] = useState({
        nombre: "",
        email: "",
        password: "",
        rol: "usuario"
    });

    const [mensaje, setMensaje] = useState("");

    const handleSubmit = async (e) => {
        e.preventDefault();
        const respuesta = await registrarUsuario(formData);
        setMensaje(respuesta.mensaje || respuesta.error);
    };

    return (
        <div>
            <h2>Registro de Usuario</h2>
            <form onSubmit={handleSubmit}>
                <input type="text" name="nombre" placeholder="Nombre" onChange={handleChange} />
                <input type="email" name="email" placeholder="Email" onChange={handleChange} />
                <input type="password" name="password" placeholder="Contraseña" onChange={handleChange} required />
                <button type="submit">Registrar</button>
            </form>
            {mensaje && <p>{mensaje}</p>}
        </div>
    );
};

export default Registro;