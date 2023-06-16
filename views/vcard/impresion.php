<style>
    div{
        font-family: 'Courier New', Courier, monospace;
        text-align: center;
        color: white;
    }
    .vcard {
        width: 100%;
        display: flex;
        border: 3px solid hsl(231, 76%, 20%);
        border-radius: 2%;
        padding: 1rem;
        min-height: 100vh;
        height: 100%;
        background-color: hsl(199, 98%, 36%);
    }

    .qr-box{
        width: 100%;
        border: 3px solid hsl(231, 76%, 20%);
        border-radius: 2%;
        padding: 1px;
        text-align: center;
    }

    .text-box{
        width: 100%;
        text-align: center;
        margin-top: 3.5rem;
    }
    td{
        color: white;
    }

    .texto{
        padding-left: 1rem;
    }
</style>

<div class="vcard">
    <div class="qr-box"> 
        <img src="<?= $nombre ?>" width='100%' />
    </div>
    <div class="text-box">
        <table>
            <tr>
                <td>
                    <img src="./images/cit.png" width="65px" alt="">

                </td>
                <td class="texto">
                    <h2>CORTESIA DE:</h2>
                    <h2>COMANDO DE INFORMÁTICA Y TECNOLOGÍA</h2>
                </td>
            </tr>
        </table>
    </div>
</div>