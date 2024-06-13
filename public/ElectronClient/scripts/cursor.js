function enableCursor() {
    const pointerIco = document.getElementById("pointer");
    document.querySelector(".wrapper").addEventListener('mousemove', (evt) => {
        let x = evt.clientX;
        let y = evt.clientY;
        pointerIco.style.top = y + "px";
        pointerIco.style.left = x + "px";
    });
}

enableCursor();