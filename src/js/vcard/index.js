import { Dropdown } from 'bootstrap';

const formularioVcard = document.querySelector('#formularioVcard')
const iframeQR = document.querySelector('#iframeQR')

const generarVcard = async e  => {
    e.preventDefault();
    try {
        const url = `/vcard/API/generar`
        const body = new FormData(formularioVcard)
        const headers = new Headers();
        headers.append('X-Requested-With','fetch');
        const config = {
            method : 'POST',
            headers,
            body
        }
    
        const respuesta = await fetch(url, config);
        const data = await respuesta.json();

        console.log(data)

        iframeQR.src = `/vcard/imprimir?idImagen=${data}`
    }catch(e){
        console.log(e)
    }
}

formularioVcard.addEventListener('submit' , generarVcard)