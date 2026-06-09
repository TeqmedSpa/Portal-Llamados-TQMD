<?php
require_once __DIR__ . '/bootstrap.php';
$pageTitle = 'Sistema de Llamados — Teqmed';

// Generar token CSRF por sesión
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// Timestamp de carga del formulario: bloquea envíos en menos de 3 s (bots sin render)
$_SESSION['form_ready_at'] = time();
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<script>var CSRF_TOKEN = '<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>';</script>

<div class="tq-root" x-data="portalLlamado()" x-init="init()">
  <div class="tq-split">

    <!-- ── Hero panel ─────────────────────────────────────────────────────── -->
    <div class="tq-hero">
      <div class="tq-logo">
        <img src="assets/images/logo.png" alt="TEQMED" style="height:56px;width:auto;object-fit:contain">
      </div>
      <h1 class="tq-title">
        Llamados<br>
        <span class="accent">TEQMED SpA</span>
      </h1>
      <div class="tq-subtitle">
        Informe su desperfecto completando el formulario
      </div>
      <div class="tq-help">
        <h4>¿Necesita ayuda?</h4>
        <p>Complete el formulario con la mayor cantidad de detalles posible. Recibirá un número de ticket para el seguimiento de su solicitud.</p>
      </div>
    </div>

    <!-- ── Form panel ─────────────────────────────────────────────────────── -->
    <div class="tq-form-panel">

      <!-- Pantalla de éxito -->
      <template x-if="enviado">
        <div class="tq-success">
          <div class="tq-success-check">
            <svg width="42" height="42" viewBox="0 0 24 24" fill="none">
              <path d="M5 12.5l4.5 4.5L19 7" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <h2>¡Llamado recepcionado!</h2>
          <p>Hemos recibido tu solicitud. El equipo técnico tomará contacto contigo a la brevedad.</p>
          <div class="tq-ticket-number" x-text="'N° de ticket · ' + numeroTicket"></div>
          <button type="button" class="tq-btn tq-btn-secondary" style="margin-top:24px"
                  @click="location.reload()">
            Crear otro llamado
          </button>
        </div>
      </template>

      <!-- Formulario wizard -->
      <template x-if="!enviado">
        <div style="display:flex;flex-direction:column;flex:1;min-height:0;overflow:hidden">

          <!-- Barra de progreso -->
          <div class="tq-progress-bar">
            <span>Página <span x-text="pagina"></span> de 2</span>
            <span class="req">* Obligatorio</span>
          </div>
          <div class="tq-progress-track">
            <div class="tq-progress-fill" :style="'width:' + (pagina === 1 ? '50' : '100') + '%'"></div>
          </div>

          <!-- ── PÁGINA 1: Datos de contacto ──────────────────────────────── -->
          <div x-show="pagina === 1" class="tq-form-scroll">
            <div class="tq-section-head">
              <h2>Datos de contacto</h2>
              <p>Entréguenos sus datos para poder contactarnos con usted.</p>
            </div>

            <!-- 1. Clínica / Centro médico -->
            <div class="tq-q">
              <label class="tq-q-label">
                <span class="num">1.</span> Clínica / Centro médico <span class="req">*</span>
              </label>
              <div class="tq-ac">
                <input class="tq-input" type="text"
                  x-model="centroInput"
                  @input.debounce.300ms="buscarCentros()"
                  @focus="mostrarCentros = centrosSugeridos.length > 0"
                  @blur="setTimeout(() => mostrarCentros = false, 150)"
                  @input="if (centroId && centroInput !== centroNombre) { centroId = null; centroNombre = ''; }"
                  placeholder="Escribe al menos 2 caracteres para buscar…"
                  autocomplete="off">
                <div class="tq-ac-list" x-show="mostrarCentros" style="display:none">
                  <template x-if="centrosSugeridos.length === 0">
                    <div class="tq-ac-empty">Sin resultados</div>
                  </template>
                  <template x-for="c in centrosSugeridos" :key="c.id">
                    <div class="tq-ac-item" @mousedown.prevent="seleccionarCentro(c)">
                      <span x-text="c.nombre"></span>
                    </div>
                  </template>
                </div>
              </div>
              <div class="tq-q-hint">Busca por nombre de clínica o centro médico.</div>
            </div>

            <!-- 2. Nombre y apellido -->
            <div class="tq-q">
              <label class="tq-q-label">
                <span class="num">2.</span> Nombre y apellido
                <span class="req">*</span>
                <span class="tq-autofill-pill" x-show="encargadoId">✓ Cargado</span>
              </label>
              <div class="tq-ac">
                <input class="tq-input" type="text"
                  x-model="nombreInput"
                  @input.debounce.300ms="buscarEncargados()"
                  @focus="mostrarEncargados = encargadosSugeridos.length > 0"
                  @blur="setTimeout(() => mostrarEncargados = false, 150)"
                  @input="if (encargadoId) encargadoId = null"
                  :disabled="!centroId"
                  placeholder="Ej: María González"
                  autocomplete="off">
                <div class="tq-ac-list" x-show="mostrarEncargados" style="display:none">
                  <template x-if="encargadosSugeridos.length === 0">
                    <div class="tq-ac-empty">Sin resultados</div>
                  </template>
                  <template x-for="c in encargadosSugeridos" :key="c.id">
                    <div class="tq-ac-item" @mousedown.prevent="seleccionarEncargado(c)">
                      <div><strong x-text="c.primer_nombre + ' ' + c.primer_apellido"></strong></div>
                      <div class="sub" x-text="c.cargo || 'Sin cargo'"></div>
                    </div>
                  </template>
                </div>
              </div>
              <div class="tq-q-hint" x-show="!centroId">Selecciona primero un centro médico.</div>
              <div class="tq-q-hint" x-show="centroId && !encargadoId">
                Escribe tu nombre para buscar en el listado del centro.
              </div>
            </div>
          </div>

          <!-- Honeypot anti-bot (oculto vía CSS) -->
          <div style="position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden" aria-hidden="true">
            <input type="text" x-model="hp" name="website" tabindex="-1" autocomplete="off">
          </div>

          <!-- ── PÁGINA 2: Datos de la falla ─────────────────────────────── -->
          <div x-show="pagina === 2" class="tq-form-scroll">
            <div class="tq-section-head">
              <h2>Datos de la falla</h2>
              <p>Detállenos cuál es el desperfecto que se presentó.</p>
            </div>

            <!-- Error de envío -->
            <div class="tq-error" x-show="errorMsg" style="display:none">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;margin-top:1px">
                <path d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <span x-text="errorMsg"></span>
            </div>

            <!-- ── Cards de equipo ──────────────────────────────────────── -->
            <template x-for="(eq, i) in equipos" :key="eq.uid">
              <div class="tq-equipo-card">

                <!-- Cabecera de la card -->
                <div class="tq-equipo-head">
                  <span class="chip" x-text="'EQUIPO ' + (i + 1)"></span>
                  <button type="button" class="tq-equipo-remove"
                          x-show="equipos.length > 1"
                          @click="quitarEquipo(i)">
                    Quitar
                  </button>
                </div>

                <!-- ① Tipo de equipo -->
                <div class="tq-q">
                  <label class="tq-q-label">
                    Tipo de equipo <span class="req">*</span>
                  </label>
                  <select class="tq-select"
                          x-model="eq.tipo"
                          @change="cambiarTipo(i)">
                    <option value="">Selecciona el tipo de equipo…</option>
                    <template x-for="t in tiposDisponibles()" :key="t">
                      <option :value="t" x-text="t"></option>
                    </template>
                  </select>
                </div>

                <!-- ② ID del equipo (autocomplete filtrado por tipo) -->
                <div class="tq-q" x-show="eq.tipo">
                  <label class="tq-q-label">
                    ID del equipo <span class="req">*</span>
                  </label>
                  <div class="tq-ac">
                    <input class="tq-input" type="text"
                      x-model="eq.equipoInput"
                      @focus="eq.mostrarDropdown = equiposPorTipo(eq.tipo, eq.equipoInput).length > 0"
                      @blur="setTimeout(() => eq.mostrarDropdown = false, 150)"
                      @input="eq.equipo_id = null; eq.tieneAbierto = false; eq.equipoModelo = ''; eq.equipoSerie = ''; eq.mostrarDropdown = equiposPorTipo(eq.tipo, eq.equipoInput).length > 0"
                      placeholder="Escribe el ID del equipo para filtrar…"
                      autocomplete="off">
                    <div class="tq-ac-list" x-show="eq.mostrarDropdown" style="display:none">
                      <template x-if="equiposPorTipo(eq.tipo, eq.equipoInput).length === 0">
                        <div class="tq-ac-empty">
                          <span x-text="todosEquipos.length === 0 ? 'Cargando equipos…' : 'Sin equipos disponibles para este tipo'"></span>
                        </div>
                      </template>
                      <template x-for="sug in equiposPorTipo(eq.tipo, eq.equipoInput)" :key="sug.id">
                        <div class="tq-ac-item" @mousedown.prevent="seleccionarEquipo(i, sug)">
                          <div>
                            <strong x-text="sug.marca + ' ' + sug.modelo"></strong>
                          </div>
                          <div class="sub">
                            <span x-text="'ID: ' + sug.numero_equipo_id"></span>
                            <template x-if="sug.tiene_llamado_abierto">
                              <span style="color:#b45309;font-weight:700"> ⚠ llamado abierto</span>
                            </template>
                          </div>
                        </div>
                      </template>
                    </div>
                  </div>
                </div>

                <!-- ③ Modelo y número de serie (auto-fill) -->
                <div class="tq-q" x-show="eq.equipo_id">
                  <label class="tq-q-label">
                    Modelo y número de serie
                    <span class="tq-autofill-pill">✓ Auto</span>
                  </label>
                  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px">
                    <div>
                      <div style="font-size:11px;font-weight:700;color:var(--tq-muted);margin-bottom:4px;text-transform:uppercase;letter-spacing:0.04em">Marca</div>
                      <div class="tq-autofilled" x-text="eq.equipoMarca"></div>
                    </div>
                    <div>
                      <div style="font-size:11px;font-weight:700;color:var(--tq-muted);margin-bottom:4px;text-transform:uppercase;letter-spacing:0.04em">Modelo</div>
                      <div class="tq-autofilled" x-text="eq.equipoModelo"></div>
                    </div>
                    <div>
                      <div style="font-size:11px;font-weight:700;color:var(--tq-muted);margin-bottom:4px;text-transform:uppercase;letter-spacing:0.04em">N° de serie</div>
                      <div class="tq-autofilled" style="font-family:monospace;font-size:13px" x-text="eq.equipoSerie"></div>
                    </div>
                  </div>
                  <!-- Error bloqueante: llamado abierto -->
                  <div class="tq-error" x-show="eq.tieneAbierto" style="margin-top:12px">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;margin-top:1px">
                      <path d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span>Este equipo ya tiene un llamado abierto. No es posible abrir uno nuevo hasta que el llamado anterior sea cerrado por el equipo técnico.</span>
                  </div>
                </div>

                <!-- ④ Falla presentada -->
                <div class="tq-q" x-show="eq.equipo_id && !eq.tieneAbierto">
                  <label class="tq-q-label">
                    Falla presentada <span class="req">*</span>
                  </label>
                  <textarea class="tq-textarea"
                    x-model="eq.falla"
                    placeholder="Describe la falla con detalle: qué ocurrió, qué síntomas presenta…"></textarea>
                </div>

                <!-- ⑤⑥ Momento y Estado (lado a lado) -->
                <div class="tq-q" x-show="eq.equipo_id && !eq.tieneAbierto">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start">

                  <div>
                    <label class="tq-q-label">
                      Momento en que se presentó la falla <span class="req">*</span>
                    </label>
                    <div class="tq-radio-group">
                      <template x-for="m in momentosParaTipo(eq.tipo)" :key="m">
                        <div class="tq-radio"
                             :class="eq.momento === m ? 'is-active' : ''"
                             @click="eq.momento = m">
                          <span class="tq-radio-dot"></span>
                          <span x-text="m"></span>
                        </div>
                      </template>
                    </div>
                  </div>

                  <div>
                    <label class="tq-q-label">
                      Estado actual de la máquina <span class="req">*</span>
                    </label>
                    <div class="tq-radio-group">
                      <div class="tq-radio tq-radio-lg"
                           :class="eq.operativo === true ? 'is-active' : ''"
                           @click="eq.operativo = true">
                        <span class="tq-radio-dot"></span>
                        <div>
                          <div style="font-weight:700">Operativo</div>
                          <div style="font-size:12px;color:var(--tq-muted);font-weight:500;margin-top:2px">La máquina sigue funcionando</div>
                        </div>
                      </div>
                      <div class="tq-radio tq-radio-lg"
                           :class="eq.operativo === false ? 'is-active is-fuera' : ''"
                           @click="eq.operativo = false">
                        <span class="tq-radio-dot"></span>
                        <div>
                          <div style="font-weight:700">Fuera de servicio</div>
                          <div style="font-size:12px;color:var(--tq-muted);font-weight:500;margin-top:2px">La máquina no puede ser utilizada</div>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
                </div>

                <!-- ⑦ Comentarios extra (opcional) -->
                <div class="tq-q" x-show="eq.equipo_id && !eq.tieneAbierto" style="margin-bottom:0">
                  <label class="tq-q-label" style="font-weight:600">
                    Comentarios extra
                    <span style="font-weight:400;color:var(--tq-muted);font-size:13px;margin-left:6px">— opcional</span>
                  </label>
                  <textarea class="tq-textarea"
                    x-model="eq.comentarios"
                    style="min-height:80px"
                    placeholder="Cualquier información adicional útil para el técnico…"></textarea>
                </div>

              </div>
            </template>

            <button type="button" class="tq-add-equipo" @click="agregarEquipo()">
              + Agregar otro equipo a este llamado
            </button>
          </div>

          <!-- ── Botones de acción ─────────────────────────────────────────── -->
          <div class="tq-actions">
            <button type="button" class="tq-btn tq-btn-secondary"
                    x-show="pagina === 2" @click="pagina = 1">
              ← Atrás
            </button>
            <span x-show="pagina === 1"></span>

            <button type="button" class="tq-btn tq-btn-primary"
                    x-show="pagina === 1"
                    @click="siguiente()"
                    :disabled="!paginaValida()">
              Siguiente →
            </button>

            <button type="button" class="tq-btn tq-btn-primary"
                    x-show="pagina === 2"
                    @click="enviar()"
                    :disabled="!pagina2Valida() || enviando">
              <span class="tq-spinner" x-show="enviando"></span>
              <span x-show="!enviando">Enviar llamado</span>
              <span x-show="enviando">Enviando…</span>
            </button>
          </div>

        </div>
      </template>

    </div><!-- /tq-form-panel -->
  </div><!-- /tq-split -->
</div><!-- /tq-root -->

<script>
function portalLlamado() {
  return {
    pagina: 1,
    enviado: false,
    enviando: false,
    numeroTicket: '',
    errorMsg: '',
    csrfToken: typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '',
    hp: '',

    // Página 1 — Centro
    centroInput: '',
    centroId: null,
    centroNombre: '',
    centrosSugeridos: [],
    mostrarCentros: false,

    // Página 1 — Contacto
    nombreInput: '',
    encargadoId: null,
    encargadosSugeridos: [],
    mostrarEncargados: false,

    // Página 2
    todosEquipos: [],
    equipos: [],

    // Momentos por tipo de equipo
    momentosPorTipo: {
      'Monitor de Hemodiálisis': [
        'En preparación',
        'En diálisis',
        'En desinfección',
        'Al encender el equipo',
        'Otros',
      ],
      'Autoclave / Esterilizador': [
        'En preparación',
        'En desinfección',
        'Al encender el equipo',
        'Otros',
      ],
      'Ventilador Mecánico': [
        'En preparación',
        'Durante uso en paciente',
        'Al encender el equipo',
        'Otros',
      ],
      'Equipo de Anestesia': [
        'En preparación',
        'Durante procedimiento',
        'Al encender el equipo',
        'Otros',
      ],
    },
    momentosDefault: [
      'En preparación',
      'Durante examen / procedimiento',
      'Al encender el equipo',
      'Otros',
    ],

    init() {
      this.equipos = [this.nuevoEquipo()];
    },

    nuevoEquipo() {
      return {
        uid: Date.now() + '-' + Math.random().toString(36).slice(2),
        tipo: '',
        equipo_id: null,
        equipoInput: '',
        mostrarDropdown: false,
        equipoMarca: '',
        equipoModelo: '',
        equipoSerie: '',
        falla: '',
        momento: '',
        operativo: null,
        comentarios: '',
        tieneAbierto: false,
      };
    },

    // ── Centro autocomplete ────────────────────────────────────────────────
    async buscarCentros() {
      if (this.centroInput.trim().length < 2) {
        this.centrosSugeridos = [];
        this.mostrarCentros = false;
        return;
      }
      try {
        const r = await fetch('process/buscar_centros.php?q=' + encodeURIComponent(this.centroInput));
        this.centrosSugeridos = await r.json();
        this.mostrarCentros = this.centrosSugeridos.length > 0;
      } catch(e) {}
    },

    seleccionarCentro(c) {
      this.centroInput = c.nombre;
      this.centroNombre = c.nombre;
      this.centroId = c.id;
      this.mostrarCentros = false;
      this.nombreInput = '';
      this.encargadoId = null;
      this.encargadosSugeridos = [];
      this.equipos = [this.nuevoEquipo()];
      this.todosEquipos = [];
      this.cargarEquipos();
    },

    // ── Contacto autocomplete ──────────────────────────────────────────────
    async buscarEncargados() {
      if (this.nombreInput.trim().length < 2 || !this.centroId) {
        this.encargadosSugeridos = [];
        this.mostrarEncargados = false;
        return;
      }
      try {
        const r = await fetch(
          'process/buscar_contactos.php?q=' + encodeURIComponent(this.nombreInput) +
          '&centro_id=' + this.centroId
        );
        this.encargadosSugeridos = await r.json();
        this.mostrarEncargados = this.encargadosSugeridos.length > 0;
      } catch(e) {}
    },

    seleccionarEncargado(c) {
      this.nombreInput = c.primer_nombre + ' ' + c.primer_apellido;
      this.encargadoId = c.id;
      this.mostrarEncargados = false;
    },

    // ── Equipos ───────────────────────────────────────────────────────────
    async cargarEquipos() {
      if (!this.centroId) return;
      try {
        const r = await fetch('process/buscar_equipos.php?centro_id=' + this.centroId);
        this.todosEquipos = await r.json();
      } catch(e) {}
    },

    tiposDisponibles() {
      const tipos = [...new Set(this.todosEquipos.map(e => e.tipo))].sort();
      return tipos;
    },

    equiposPorTipo(tipo, input) {
      if (!tipo) return [];
      let lista = this.todosEquipos.filter(e => e.tipo === tipo);
      if (input && input.trim().length >= 1) {
        const q = input.toLowerCase();
        lista = lista.filter(e => e.label.toLowerCase().includes(q));
      }
      return lista.slice(0, 8);
    },

    seleccionarEquipo(i, sug) {
      const eq = this.equipos[i];
      eq.equipo_id = sug.id;
      eq.equipoInput = sug.numero_equipo_id || sug.numero_serie;
      eq.equipoMarca = sug.marca;
      eq.equipoModelo = sug.modelo;
      eq.equipoSerie = sug.numero_serie;
      eq.tieneAbierto = sug.tiene_llamado_abierto;
      eq.mostrarDropdown = false;
    },

    cambiarTipo(i) {
      const eq = this.equipos[i];
      eq.equipo_id = null;
      eq.equipoInput = '';
      eq.equipoMarca = '';
      eq.equipoModelo = '';
      eq.equipoSerie = '';
      eq.tieneAbierto = false;
      eq.momento = '';
    },

    momentosParaTipo(tipo) {
      return this.momentosPorTipo[tipo] || this.momentosDefault;
    },

    agregarEquipo() {
      this.equipos.push(this.nuevoEquipo());
    },

    quitarEquipo(i) {
      this.equipos.splice(i, 1);
    },

    // ── Validación ────────────────────────────────────────────────────────
    paginaValida() {
      return !!this.centroId && !!this.encargadoId;
    },

    pagina2Valida() {
      if (this.equipos.length === 0) return false;
      return this.equipos.every(eq =>
        eq.tipo &&
        eq.equipo_id &&
        !eq.tieneAbierto &&
        eq.falla.trim().length >= 5 &&
        eq.momento &&
        eq.operativo !== null
      );
    },

    // ── Navegación ────────────────────────────────────────────────────────
    siguiente() {
      if (!this.paginaValida()) return;
      if (this.todosEquipos.length === 0 && this.centroId) this.cargarEquipos();
      this.pagina = 2;
      this.$nextTick(() => {
        const scroll = document.querySelector('.tq-form-scroll');
        if (scroll) scroll.scrollTop = 0;
      });
    },

    // ── Envío ─────────────────────────────────────────────────────────────
    async enviar() {
      if (!this.pagina2Valida() || this.enviando) return;
      this.enviando = true;
      this.errorMsg = '';
      try {
        const r = await fetch('process/procesar_llamado.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            csrf_token: this.csrfToken,
            hp: this.hp,
            encargado_id: this.encargadoId,
            equipos: this.equipos.map(eq => {
              const partes = [eq.falla.trim()];
              if (eq.momento) partes.push('Momento: ' + eq.momento);
              if (eq.comentarios.trim()) partes.push('Comentarios: ' + eq.comentarios.trim());
              return {
                equipo_id: eq.equipo_id,
                descripcion_problema: partes.join('\n'),
                operativo: eq.operativo,
              };
            })
          })
        });
        const data = await r.json();
        if (data.success) {
          if (data.csrf_token) this.csrfToken = data.csrf_token;
          this.enviado = true;
          this.numeroTicket = data.numero;
        } else {
          this.errorMsg = data.message;
        }
      } catch(e) {
        this.errorMsg = 'Error de conexión. Intente nuevamente.';
      } finally {
        this.enviando = false;
      }
    },

  };
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
