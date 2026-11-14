/**
 * Nampula Fodd - JavaScript principal
 */
document.addEventListener('DOMContentLoaded', function () {

    // Menu mobile
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');
    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', function () {
            navLinks.classList.toggle('activo');
        });
    }

    // GPS "Perto de mim"
    const btnGps = document.getElementById('btnGps');
    if (btnGps) {
        btnGps.addEventListener('click', function () {
            if (!navigator.geolocation) {
                alert('O seu navegador não suporta geolocalização.');
                return;
            }
            btnGps.innerHTML = '<i class="fas fa-spinner fa-spin"></i> A localizar...';
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    // Usa o data-base-url se existir, senão tenta detectar
                    const base = document.body.dataset.base || '';
                    window.location.href = base + '/restaurantes.php?lat=' + lat + '&lng=' + lng;
                },
                function () {
                    alert('Não foi possível obter a sua localização. Verifique as permissões.');
                    btnGps.innerHTML = '<i class="fas fa-location-arrow"></i> Perto de mim';
                }
            );
        });
    }

    // Adicionar ao carrinho (AJAX)
    document.querySelectorAll('.btn-add-carrinho').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const produtoId = this.dataset.id;
            const base = document.body.dataset.base || '';
            const url = base + '/cliente/adicionar_carrinho.php';

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'produto_id=' + produtoId + '&quantidade=1'
            })
            .then(r => r.json())
            .then(data => {
                if (data.sucesso) {
                    this.innerHTML = '<i class="fas fa-check"></i> Adicionado!';
                    this.style.background = '#28a745';
                    setTimeout(() => {
                        this.innerHTML = '<i class="fas fa-plus"></i> Adicionar';
                        this.style.background = '';
                    }, 1500);
                } else {
                    alert(data.mensagem || 'Erro ao adicionar. Faça login como cliente.');
                }
            })
            .catch(() => alert('Erro de conexão. Verifique se está logado como cliente.'));
        });
    });

    // Confirmação de ações
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });
});
