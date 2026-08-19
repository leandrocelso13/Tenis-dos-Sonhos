<?php
// controls.php - Barra de controles de acessibilidade
?>
<!-- CONTROLES NO TOPO -->
<div class="controls-container justify-content-center">
    <span class="font-size-display">Tamanho:</span>
    <button class="btn-font" onclick="diminuirFonte()" title="Diminuir fonte">−</button>
    <span class="font-percent" id="fontPercent">100%</span>
    <button class="btn-font" onclick="aumentarFonte()" title="Aumentar fonte">+</button>
    
    <div style="width: 1px; height: 30px; background: #ddd;"></div>
    
    <span class="font-size-display" style="font-size: 0.875rem;">Modo:</span>
    <label class="switch">
        <input type="checkbox" id="themeToggle">
        <span class="slider round"></span>
    </label>
    <span id="themeStatus" style="font-size: 0.875rem; font-weight: 600; color: #333;">Claro</span>
</div>

<!-- CSS para os controles -->
<style>
/* ===== CONTROLES NO TOPO ===== */
.controls-container {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    padding: 8px 15px;
    background: #f8f9fa;
    border-radius: 10px;
    border: 2px solid #dc3545;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    
    /* ===== CORREÇÃO PRINCIPAL ===== */
    width: 100%;
    max-width: 480px;  /* Padrão para o login */
    margin-left: auto;
    margin-right: auto;
    box-sizing: border-box;
}

.controls-container .btn-font {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1rem;
    border: 2px solid #dc3545;
    color: #dc3545;
    background: white;
    transition: all 0.3s;
    cursor: pointer;
}

.controls-container .btn-font:hover {
    background: #dc3545;
    color: white;
}

.controls-container .font-percent {
    font-size: 0.9rem;
    font-weight: 700;
    color: #dc3545;
    min-width: 45px;
    text-align: center;
}

/* ===== ALAVANCA ===== */
.switch {
    position: relative;
    display: inline-block;
    width: 45px;
    height: 26px;
}

.switch input { 
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
}

input:checked + .slider {
    background-color: #dc3545;
}

input:checked + .slider:before {
    transform: translateX(19px);
}

.slider.round {
    border-radius: 34px;
}

.slider.round:before {
    border-radius: 50%;
}

.font-size-display {
    font-size: 0.8rem;
    color: #333;
    font-weight: 600;
}

/* ===== MODO ESCURO NOS CONTROLES ===== */
[data-bs-theme="dark"] .controls-container {
    background: #2d2d2d !important;
    border-color: #dc3545 !important;
}

[data-bs-theme="dark"] .controls-container .btn-font {
    background: #3d3d3d !important;
    color: #dc3545 !important;
    border-color: #dc3545 !important;
}

[data-bs-theme="dark"] .controls-container .btn-font:hover {
    background: #dc3545 !important;
    color: white !important;
}

[data-bs-theme="dark"] .controls-container .font-percent {
    color: #dc3545 !important;
}

[data-bs-theme="dark"] .controls-container .font-size-display,
[data-bs-theme="dark"] #themeStatus {
    color: #ffffff !important;
}

/* ===== RESPONSIVO PARA O CADASTRO (800px) ===== */
@media (min-width: 600px) {
    .controls-container {
        max-width: 800px;  /* Para o cadastro */
    }
}
</style>

<!-- JavaScript para os controles -->
<script>
// ===== ALTERNAR TEMA =====
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('themeToggle');
    const themeStatus = document.getElementById('themeStatus');
    
    if (themeToggle) {
        // Carregar tema salvo
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
            themeToggle.checked = true;
            themeStatus.textContent = 'Escuro';
            themeStatus.style.color = '#ffffff';
        }
        
        themeToggle.addEventListener('change', function() {
            if (this.checked) {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
                themeStatus.textContent = 'Escuro';
                themeStatus.style.color = '#ffffff';
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-bs-theme', 'light');
                themeStatus.textContent = 'Claro';
                themeStatus.style.color = '#333';
                localStorage.setItem('theme', 'light');
            }
        });
    }
});

// ===== AUMENTAR E DIMINUIR FONTE =====
let fontSize = 100;

function aumentarFonte() {
    if (fontSize < 200) {
        fontSize += 10;
        aplicarFonte();
    }
}

function diminuirFonte() {
    if (fontSize > 60) {
        fontSize -= 10;
        aplicarFonte();
    }
}

function aplicarFonte() {
    document.documentElement.style.fontSize = fontSize + '%';
    const fontPercent = document.getElementById('fontPercent');
    if (fontPercent) {
        fontPercent.textContent = fontSize + '%';
    }
    localStorage.setItem('fontSize', fontSize);
}

// Carregar tamanho salvo ao iniciar
document.addEventListener('DOMContentLoaded', function() {
    const savedSize = localStorage.getItem('fontSize');
    if (savedSize) {
        fontSize = parseInt(savedSize);
        aplicarFonte();
    }
});
</script>