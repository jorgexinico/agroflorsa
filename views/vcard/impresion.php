<style>
    div{
        font-family: 'Courier New', Courier, monospace;
        text-align: center;
        color: rgb(2,156,223) ;
    }
    .vcard {
        width: 100%;
        display: flex;
        border: 3px solid rgb(2,156,223);
        border-radius: 2%;
        padding: 1rem;
        min-height: 100vh;
        height: 100%;
        background-color: hsl(196, 74%, 91%);
    }

    .qr-box{
        width: 100%;
        border: 3px solid rgb(2,156,223);
        border-radius: 2%;
        padding: 1px;
        text-align: center;
    }

    .text-box{
        width: 100%;
        text-align: center;
    }
</style>

<div class="vcard">
    <div class="qr-box"> 
        <img src="<?= $nombre ?>" width='100%' />
    </div>
    <div class="text-box">
        <h2>CORTESIA DE:</h2>
        <h2>COMANDO DE INFORMÁTICA Y TECNOLOGÍA</h2>
        <img src="./images/cit.png" width="65px" alt="">
    </div>
</div>