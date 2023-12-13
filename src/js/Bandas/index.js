import { Dropdown } from "bootstrap";
import { validarFormulario, Toast } from "../funciones";
import Datatable from "datatables.net-bs5";
import { lenguaje } from "../lenguaje";
import Swal from "sweetalert2";

const formBandas = document.getElementById("formBandas");
const btnGuardar = document.getElementById("btnGuardar");
const btnModificar = document.getElementById("btnModificar");
const divTabla = document.getElementById("divTabla");
let tablaBandas = new Datatable("#BandasTabla");

btnModificar.parentElement.style.display = "none";
btnGuardar.disabled = false;
btnModificar.disabled = true;

const guardarBandas = async (evento) => {
  evento.preventDefault();

  let formularioValido = validarFormulario(formBandas, ["id"]);

  if (!formularioValido) {
    Toast.fire({
      icon: "warning",
      title: "Debe llenar todos los campos",
    });
    return;
  }
//     console.log(data);
//   return;
  try {
    //Crear el cuerpo de la consulta
    const url = "/servicio-musicas/API/Bandas/guardar";
    const body = new FormData(formBandas);
    body.delete("id");
    const headers = new Headers();
    headers.append("X-requested-With", "fetch");

    const config = {
      method: "POST",
      headers,
      body,
    };

    const respuesta = await fetch(url, config);
    const data = await respuesta.json();
    // console.log(data);
    // return;
    const { resultado } = data;

    if (resultado == 1) {
      Toast.fire({
        icon: "success",
        title: "Registro guardado",
      });

      formBandas.reset();
      buscarBandas();
    } else {
      Toast.fire({
        icon: "error",
        title: "Ocurrió un error",
      });
    }
  } catch (error) {
    console.log(error);
  }
};

const buscarBandas = async (evento) => {
  evento && evento.preventDefault();

  try {
    const url = "/servicio-musicas/API/Bandas/buscar";
    const headers = new Headers();
    headers.append("X-requested-With", "fetch");

    const config = {
      method: "GET",
    };

    const respuesta = await fetch(url, config);
    const data = await respuesta.json();

    // console.log(data);
    //   return;

    tablaBandas.destroy();
    let contador = 1;
    tablaBandas = new Datatable("#BandasTabla", {
      language: lenguaje,
      data: data,
      columns: [
        {
          data: "id",
          render: () => {
            return contador++;
          },
        },
        { data: "banda" },

        {
          data: "id",
          render: (data, type, row, meta) => {
            return `<button class="btn btn-warning" onclick="asignarValores('${row.id}', '${row.banda}')">Modificar</button>`;
          },
        },
        {
          data: "id",
          render: (data, type, row, meta) => {
            return `<button class="btn btn-danger" onclick="eliminarRegistro('${row.id}')">Eliminar</button>`;
          },
        },
      ],
    });
  } catch (error) {
    console.log(error);
  }
};

const modificarBandas = async (evento) => {
  evento.preventDefault();

  let formularioValido = validarFormulario(formBandas);

  if (!formularioValido) {
    Toast.fire({
      icon: "warning",
      title: "Debe llenar todos los campos",
    });
    return;
  }

  try {
    //Crear el cuerpo de la consulta
    const url = "/servicio-musicas/API/Bandas/modificar";
    const body = new FormData(formBandas);
    const headers = new Headers();
    headers.append("X-requested-With", "fetch");

    const config = {
      method: "POST",
      headers,
      body,
    };

    const respuesta = await fetch(url, config);
    const data = await respuesta.json();

    const { resultado } = data;
    // const resultado = data.resultado;

    if (resultado == 1) {
      Toast.fire({
        icon: "success",
        title: "Registro modificado",
      });
      buscarBandas();
      formBandas.reset();
      btnModificar.parentElement.style.display = "none";
      btnGuardar.parentElement.style.display = "";
      btnGuardar.disabled = false;
      btnModificar.disabled = true;

      divTabla.style.display = "";
    } else {
      Toast.fire({
        icon: "error",
        title: "Ocurrió un error",
      });
    }
  } catch (error) {
    console.log(error);
  }
};

buscarBandas();

window.asignarValores = (id, banda) => {
  formBandas.id.value = id;
  formBandas.banda.value = banda;

  btnModificar.parentElement.style.display = "";
  btnGuardar.parentElement.style.display = "none";
  btnGuardar.disabled = true;
  btnModificar.disabled = false;

  divTabla.style.display = "none";
};

window.eliminarRegistro = (id) => {
  Swal.fire({
    title: "Confirmación",
    icon: "warning",
    text: "¿Esta seguro que desea eliminar este registro?",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si, eliminar",
  }).then(async (result) => {
    if (result.isConfirmed) {
      const url = "/servicio-musicas/API/Bandas/eliminar";
      const body = new FormData();
      body.append("id", id);
      const headers = new Headers();
      headers.append("X-requested-With", "fetch");

      const config = {
        method: "POST",
        headers,
        body,
      };

      const respuesta = await fetch(url, config);
      const data = await respuesta.json();
      const { resultado } = data;
      // const resultado = data.resultado;
      //   console.log(data);

      if (resultado == 1) {
        Toast.fire({
          icon: "success",
          title: "Registro eliminado",
        });

        // formBandas.reset();
        buscarBandas();
      } else {
        Toast.fire({
          icon: "error",
          title: "Ocurrió un error",
        });
      }
    }
  });
};

formBandas.addEventListener("submit", guardarBandas);
btnModificar.addEventListener("click", modificarBandas);
