const menuItems = document.querySelectorAll('.nav-item');
const container = document.getElementById('conteudoModulo');
const titulo = document.getElementById('tituloModulo');

/* Carrega módulo */
function carregarModulo(modulo, tituloTexto) {
    fetch(`modules/${modulo}.php`, {
        credentials: "include"
    })
        .then(res => {
            if (!res.ok) throw new Error();
            return res.text();
        })
        .then(html => {
            container.innerHTML = html;
            titulo.innerText = tituloTexto;
            carregarJSModulo(modulo);
        })
        .catch(() => {
            container.innerHTML = `
                <p style="color:red">Erro ao carregar o módulo</p>
            `;
        });
}

/* Carrega JS do módulo */
function carregarJSModulo(modulo) {
    const scriptAntigo = document.getElementById('js-modulo');
    if (scriptAntigo) scriptAntigo.remove();

    const script = document.createElement('script');
    script.id = 'js-modulo';
    script.src = `modules/${modulo}.js`;
    script.defer = true;

    script.onload = () => {
        // chama init se existir
        const init = window[`init${capitalize(modulo)}`];
        if (typeof init === "function") {
            init();
        }
    };

    document.body.appendChild(script);
}

/* Util */
function capitalize(text) {
    return text.charAt(0).toUpperCase() + text.slice(1);
}

/* Clique no menu */
menuItems.forEach(item => {
    item.addEventListener('click', e => {
        e.preventDefault();

        menuItems.forEach(i => i.classList.remove('active'));
        item.classList.add('active');

        const modulo = item.dataset.module;
        const tituloTexto = item.innerText.trim();

        carregarModulo(modulo, tituloTexto);
    });
});

/* Formata para real */
function formatReal(valor) {
    return Number(valor).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    });
}
/* Carrega HOME ao iniciar */
carregarModulo('home', 'Home');
