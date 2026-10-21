// Cliente WebSocket da sala de leilão.
// Toda validação de verdade acontece no servidor; aqui só exibimos o estado.
(() => {
    const sala = document.getElementById('sala');
    const { wsUrl, token } = sala.dataset;
    const leilaoId = Number(sala.dataset.leilaoId);
    const usuarioId = Number(sala.dataset.usuarioId);

    const el = {
        status: document.getElementById('status-conexao'),
        maiorValor: document.getElementById('maior-valor'),
        maiorUsuario: document.getElementById('maior-usuario'),
        maiorLance: document.getElementById('maior-lance'),
        proximoMinimo: document.getElementById('proximo-minimo'),
        lanceRapido: document.getElementById('lance-rapido'),
        form: document.getElementById('formulario-lance'),
        valorLivre: document.getElementById('valor-livre'),
        aviso: document.getElementById('aviso'),
        presenca: document.getElementById('presenca'),
        historico: document.getElementById('historico'),
        cronometro: document.getElementById('cronometro'),
    };

    const moeda = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
    const formatar = (centavos) => moeda.format(centavos / 100);
    const hora = (texto) => new Date(texto.replace(' ', 'T')).toLocaleTimeString('pt-BR');

    let socket;
    let leilao = null;
    let tentativas = 0;
    let fimEm = Date.now() + Number(el.cronometro.dataset.segundos) * 1000;

    function conectar() {
        definirStatus('conectando', 'Conectando…');
        socket = new WebSocket(wsUrl);

        socket.addEventListener('open', () => {
            tentativas = 0;
            socket.send(JSON.stringify({ tipo: 'entrar', token, leilaoId }));
        });

        socket.addEventListener('message', (evento) => {
            const msg = JSON.parse(evento.data);
            const tratar = {
                estado: () => {
                    definirStatus('online', 'Ao vivo');
                    el.historico.replaceChildren();
                    [...msg.lances].reverse().forEach((l) => adicionarLance(l, false));
                    atualizarLeilao(msg.leilao);
                },
                novo_lance: () => {
                    adicionarLance(msg.lance, true);
                    atualizarLeilao(msg.leilao);
                    if (msg.lance.usuarioId === usuarioId) {
                        avisar(`Seu lance de ${formatar(msg.lance.valor)} foi aceito.`, 'sucesso');
                    }
                },
                encerrado: () => {
                    atualizarLeilao(msg.leilao);
                    avisar('Leilão encerrado. Não são aceitos novos lances.', 'erro');
                },
                presenca: () => {
                    const n = msg.participantes.length;
                    el.presenca.textContent = `${n} ${n === 1 ? 'pessoa' : 'pessoas'} na sala: ${msg.participantes.join(', ')}`;
                },
                erro: () => avisar(msg.mensagem, 'erro'),
            }[msg.tipo];
            tratar?.();
        });

        socket.addEventListener('close', () => {
            definirStatus('offline', 'Reconectando…');
            habilitarLances(false);
            const espera = Math.min(1000 * 2 ** tentativas++, 10000);
            setTimeout(conectar, espera);
        });
    }

    function atualizarLeilao(dados) {
        leilao = dados;
        fimEm = Date.now() + dados.segundosRestantes * 1000;

        el.maiorValor.textContent = formatar(dados.maiorLance ?? dados.lanceInicial);
        el.proximoMinimo.textContent = formatar(dados.minimoAceito);
        el.valorLivre.placeholder = `Outro valor, mín. ${formatar(dados.minimoAceito)}`;
        if (dados.maiorLance === null) {
            el.maiorUsuario.textContent = 'nenhum lance ainda';
        }

        if (dados.encerrado) {
            definirStatus('encerrado', 'Encerrado');
            el.lanceRapido.textContent = 'Leilão encerrado';
        }
        habilitarLances(!dados.encerrado);
        atualizarCronometro();
    }

    function adicionarLance(lance, animar) {
        const item = document.createElement('li');
        item.className = lance.usuarioId === usuarioId ? 'meu' : '';
        if (animar) item.classList.add('novo');

        const quem = document.createElement('span');
        quem.textContent = lance.usuarioId === usuarioId ? `${lance.usuario} (você)` : lance.usuario;
        const valor = document.createElement('strong');
        valor.textContent = formatar(lance.valor);
        const quando = document.createElement('time');
        quando.textContent = hora(lance.criadoEm);

        item.append(quem, valor, quando);
        el.historico.prepend(item);

        el.maiorUsuario.textContent = quem.textContent;
        if (animar) {
            el.maiorLance.classList.remove('pulsar');
            void el.maiorLance.offsetWidth; // reinicia a animação
            el.maiorLance.classList.add('pulsar');
        }
    }

    function enviarLance(valor) {
        if (socket?.readyState !== WebSocket.OPEN) return;
        socket.send(JSON.stringify({ tipo: 'lance', valor }));
    }

    function habilitarLances(ativo) {
        el.lanceRapido.disabled = !ativo;
        el.valorLivre.disabled = !ativo;
        el.form.querySelector('[type=submit]').disabled = !ativo;
    }

    function definirStatus(estado, texto) {
        el.status.dataset.estado = estado;
        el.status.textContent = texto;
    }

    let timerAviso;
    function avisar(texto, tipo) {
        el.aviso.textContent = texto;
        el.aviso.className = `alerta alerta-${tipo}`;
        el.aviso.hidden = false;
        clearTimeout(timerAviso);
        timerAviso = setTimeout(() => (el.aviso.hidden = true), 4000);
    }

    function atualizarCronometro() {
        const restante = Math.max(0, Math.round((fimEm - Date.now()) / 1000));
        if (restante === 0) {
            el.cronometro.textContent = 'encerrado';
            return;
        }
        const d = Math.floor(restante / 86400);
        const h = Math.floor((restante % 86400) / 3600);
        const m = Math.floor((restante % 3600) / 60);
        const s = restante % 60;
        const pad = (n) => String(n).padStart(2, '0');
        el.cronometro.textContent = (d ? `${d}d ` : '') + `${pad(h)}:${pad(m)}:${pad(s)}`;
    }

    el.lanceRapido.addEventListener('click', () => leilao && enviarLance(leilao.minimoAceito / 100));
    el.form.addEventListener('submit', (evento) => {
        evento.preventDefault();
        const valor = el.valorLivre.value.trim();
        if (!valor) return;
        enviarLance(valor);
        el.valorLivre.value = '';
    });

    setInterval(atualizarCronometro, 1000);
    atualizarCronometro();
    conectar();
})();
