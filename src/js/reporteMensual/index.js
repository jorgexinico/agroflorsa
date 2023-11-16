import { Dropdown } from "bootstrap";
import { validarFormulario, Toast } from "../funciones";
import Datatable from "datatables.net-bs5";
import { lenguaje } from "../lenguaje";
import Swal from "sweetalert2";

const formIngresoComision = document.getElementById("formIngresoComision");
const ReporteBusqueda = document.getElementById("ReporteBusqueda");
const btnGuardar = document.getElementById("guardar_nuevo");
// const btnModificar = document.getElementById("btnModificar");
const divTabla = document.getElementById("divTabla");
let tablaFormulario = new Datatable("#FormularioTabla");
const cambiarCheck = document.querySelector("#checkcivil");
const imprimir = document.getElementById("imprimir");
const ReporteDia = document.getElementById("ReporteDia");


// btnModificar.parentElement.style.display = "none";
btnGuardar.disabled = false;
// btnModificar.disabled = true;

const guardarFormulario = async (evento) => {
  evento.preventDefault();

  try {
    //Crear el cuerpo de la consulta
    const url = "/servicio-musicas/API/reporteMensual/guardar";
    const body = new FormData(formIngresoComision);
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
    // console.log(resultado);
    // return;

    if (resultado == 1) {
      Toast.fire({
        icon: "success",
        title: "Registro guardado",
      });

      formIngresoComision.reset();
      buscarFormulario();
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

const buscarFormulario = async (evento) => {
  evento && evento.preventDefault();

  try {
    const url = "/servicio-musicas/API/reporteMensual/buscar";
    const headers = new Headers();
    headers.append("X-requested-With", "fetch");

    const config = {
      method: "GET",
    };

    const respuesta = await fetch(url, config);
    const data = await respuesta.json();

    console.log(data);
    return;

    tablaFormulario.destroy();
    let contador = 1;

    tablaFormulario = new Datatable("#FormularioTabla", {
      language: lenguaje,
      data: data,
      columns: [
        {
          data: "id",
          render: () => {
            return contador++;
          },
        },
        { data: "nombrebanda" },

        {
          data: "id",
          render: (data, type, row, meta) => {
            if (row.idcomision == 999) {
              return row.civil;
            } else {
              return row.comisionnombre;
            }
          },
        },
        // { data: "comisionnombre" },
        // { data: "civil" },
        { data: "motivo" },
        { data: "cantidad" },
        { data: "fecha" },
        { data: "horain" },
        { data: "horafin" },
        {
          data: "id",
          render: (data, type, row, meta) => {
            if (row.horaactualizada < row.horain) {
              return "PENDIENTE";
            }
            if (row.horaactualizada > row.horafin) {
              return "FINALIZADO";
            }
            if (
              row.horaactualizada > row.horain &&
              row.horaactualizada < row.horafin
            ) {
              return "EN CURSO";
            }
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

buscarFormulario();
const modificarFormulario = async (evento) => {
  evento.preventDefault();

  let formularioValido = validarFormulario(formIngresoComision);

  if (!formularioValido) {
    Toast.fire({
      icon: "warning",
      title: "Debe llenar todos los campos",
    });
    return;
  }

  try {
    //Crear el cuerpo de la consulta
    const url = "/servicio-musicas/API/reporteMensual/modificar";
    const body = new FormData(formIngresoComision);
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
      buscarFormulario();
      formIngresoComision.reset();
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

// window.asignarValores = (id, banda) => {
//   formIngresoComision.id.value = id;
//   formIngresoComision.banda.value = banda;

//   btnModificar.parentElement.style.display = "";
//   btnGuardar.parentElement.style.display = "none";
//   btnGuardar.disabled = true;
//   btnModificar.disabled = false;

//   divTabla.style.display = "none";
// };

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
      const url = "/servicio-musicas/API/reporteMensual/eliminar";
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

        // formIngresoComision.reset();
        buscarFormulario();
      } else {
        Toast.fire({
          icon: "error",
          title: "Ocurrió un error",
        });
      }
    }
  });
};

const showContent = async (evento) => {
  evento.preventDefault();

  // let cambiarCheck = e.target.value;
  // alert('cambiarCheck');
  const element = document.getElementById("divComisionMilitar");
  const element1 = document.getElementById("divComisionCivil");
  const check = document.getElementById("checkcivil");
  if (check.checked) {
    element.style.display = "none";
    element1.style.display = "";
  } else {
    element.style.display = "";
    element1.style.display = "none";
  }
};

const abrirpdf = async (e) => {
  //alert("no");
  var url = `/servicio-musicas/impresion/pdfbusqueda`;
  window.location.href = url;
};
const abrirpdfReporte = async (e) => {
  
  let fechaReporte = ReporteBusqueda.fechareporte.value
  alert(fechaReporte);
  
  var url = `/servicio-musicas/impresion/pdfbusquedaReporte?fechaReporte=${fechaReporte}`;

  window.location.href = url;
};

formIngresoComision.addEventListener("submit", guardarFormulario);
// btnModificar.addEventListener("click", modificarFormulario);
cambiarCheck.addEventListener("change", showContent);
imprimir.addEventListener("click", abrirpdf);
ReporteDia.addEventListener("click", abrirpdfReporte);
