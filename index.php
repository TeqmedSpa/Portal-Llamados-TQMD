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
        <img src="assets/images/logo.png" alt="TEQMED" style="width:60%;height:auto;object-fit:contain">
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
        <div class="tq-wizard-inner">

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

            <!-- Alerta de campos obligatorios -->
            <div class="tq-error" x-show="errorMsg" style="display:none">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;margin-top:1px">
                <path d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <span x-text="errorMsg"></span>
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
                      <div><strong x-text="c.nombre"></strong></div>
                      <div class="sub" x-text="c.cliente_nombre"></div>
                    </div>
                  </template>
                </div>
              </div>
              <div class="tq-q-hint">Busca por nombre de clínica, centro médico o cliente.</div>
            </div>

            <!-- 2. Nombre y apellido -->
            <div class="tq-q">
              <template x-if="!modoNuevoEncargado">
                <div>
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
                  <button type="button" class="tq-link-btn" x-show="centroId" @click="activarNuevoEncargado()">
                    ¿No apareces en la lista? Regístrate aquí
                  </button>
                </div>
              </template>

              <template x-if="modoNuevoEncargado">
                <div>
                  <label class="tq-q-label">
                    <span class="num">2.</span> Regístrate como encargado <span class="req">*</span>
                  </label>
                  <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div>
                      <div class="tq-q-hint" style="margin:0 0 6px;font-weight:600">Nombre <span class="req">*</span></div>
                      <input class="tq-input" type="text" x-model="nuevoEncargado.primer_nombre" placeholder="Nombre" autocomplete="off">
                    </div>
                    <div>
                      <div class="tq-q-hint" style="margin:0 0 6px;font-weight:600">Segundo nombre</div>
                      <input class="tq-input" type="text" x-model="nuevoEncargado.segundo_nombre" placeholder="Opcional" autocomplete="off">
                    </div>
                    <div>
                      <div class="tq-q-hint" style="margin:0 0 6px;font-weight:600">Apellido <span class="req">*</span></div>
                      <input class="tq-input" type="text" x-model="nuevoEncargado.primer_apellido" placeholder="Apellido" autocomplete="off">
                    </div>
                    <div>
                      <div class="tq-q-hint" style="margin:0 0 6px;font-weight:600">Segundo apellido <span class="req">*</span></div>
                      <input class="tq-input" type="text" x-model="nuevoEncargado.segundo_apellido" placeholder="Segundo apellido" autocomplete="off">
                    </div>
                    <div>
                      <div class="tq-q-hint" style="margin:0 0 6px;font-weight:600">Cargo <span class="req">*</span></div>
                      <select class="tq-select" x-model="nuevoEncargado.cargo">
                        <option value="">Selecciona…</option>
                        <template x-for="c in cargosDisponibles" :key="c">
                          <option :value="c" x-text="c"></option>
                        </template>
                      </select>
                    </div>
                    <div>
                      <div class="tq-q-hint" style="margin:0 0 6px;font-weight:600">Teléfono <span class="req">*</span></div>
                      <input class="tq-input" type="text" inputmode="numeric"
                        x-model="nuevoEncargado.telefono"
                        @input="nuevoEncargado.telefono = $event.target.value.replace(/\D/g,'').slice(0,9)"
                        placeholder="9XXXXXXXX" autocomplete="off">
                    </div>
                  </div>
                  <div style="margin-top:14px">
                    <div class="tq-q-hint" style="margin:0 0 6px;font-weight:600">Email <span class="req">*</span></div>
                    <input class="tq-input" type="email" x-model="nuevoEncargado.email" placeholder="tu@correo.cl" autocomplete="off">
                  </div>
                  <button type="button" class="tq-link-btn" @click="cancelarNuevoEncargado()">
                    ‹ Ya estoy registrado, buscar en el listado
                  </button>
                </div>
              </template>
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
                      @focus="eq.mostrarDropdown = equiposPorTipo(eq.tipo, eq.equipoInput, i).length > 0"
                      @blur="setTimeout(() => eq.mostrarDropdown = false, 150)"
                      @input="eq.equipo_id = null; eq.tieneAbierto = false; eq.equipoModelo = ''; eq.equipoSerie = ''; eq.mostrarDropdown = equiposPorTipo(eq.tipo, eq.equipoInput, i).length > 0"
                      placeholder="Escribe el ID del equipo para filtrar…"
                      autocomplete="off">
                    <div class="tq-ac-list" x-show="eq.mostrarDropdown" style="display:none">
                      <template x-if="equiposPorTipo(eq.tipo, eq.equipoInput, i).length === 0">
                        <div class="tq-ac-empty">
                          <span x-text="todosEquipos.length === 0 ? 'Cargando equipos…' : 'Sin equipos disponibles para este tipo'"></span>
                        </div>
                      </template>
                      <template x-for="sug in equiposPorTipo(eq.tipo, eq.equipoInput, i)" :key="sug.id">
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

            <!-- ── Imágenes (máximo 3) ──────────────────────────────────── -->
            <div class="tq-q" style="margin-top:24px">
              <label class="tq-q-label">
                Imágenes
                <span style="font-weight:400;color:var(--tq-muted);font-size:13px;margin-left:6px">— opcional, máximo 3</span>
              </label>
              <div class="tq-q-hint" style="margin-top:0;margin-bottom:10px">
                Puedes adjuntar fotos del equipo o de la falla para ayudar al técnico. Formatos: JPG, PNG, WEBP. Máximo 5 MB por imagen.
              </div>

              <!-- Previews -->
              <div class="tq-img-previews" x-show="imagenesPreview.length > 0">
                <template x-for="(img, idx) in imagenesPreview" :key="img.id">
                  <div class="tq-img-thumb">
                    <img :src="img.url" alt="Vista previa">
                    <button type="button" class="tq-img-remove" @click="quitarImagen(idx)" title="Quitar imagen">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                      </svg>
                    </button>
                    <span class="tq-img-name" x-text="img.name"></span>
                  </div>
                </template>
              </div>

              <!-- Botón de subir -->
              <label class="tq-upload-area" x-show="imagenes.length < 3" style="display:none">
                <input type="file" accept="image/jpeg,image/png,image/webp" multiple
                       @change="agregarImagenes($event)" style="display:none">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" style="color:var(--tq-muted)">
                  <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Haz clic para seleccionar imágenes</span>
                <span style="font-size:12px;color:var(--tq-muted)" x-text="'(' + imagenes.length + '/3 seleccionadas)'"></span>
              </label>
            </div>
          </div>

          <!-- ── Aceptación de política de privacidad ─────────────────────── -->
          <div x-show="pagina === 2" style="margin-top:20px;padding:16px 18px;background:var(--tq-surface-2);border:1px solid var(--tq-border-soft);border-radius:10px">
            <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:13px;line-height:1.5;color:var(--tq-ink-2)">
              <input type="checkbox" x-model="aceptaPrivacidad"
                     style="margin-top:3px;width:16px;height:16px;flex-shrink:0;accent-color:var(--tq-teal-700)">
              <span>
                He leído y acepto la
                <a href="/privacidad.php" target="_blank" style="color:var(--tq-teal-700);font-weight:600;text-decoration:underline">Política de Privacidad</a>
                y autorizo el tratamiento de mis datos personales conforme a lo descrito en ella.
              </span>
            </label>
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
                    @click="siguiente()">
              Siguiente →
            </button>

            <button type="button" class="tq-btn tq-btn-primary"
                    x-show="pagina === 2"
                    @click="enviar()"
                    :disabled="enviando">
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
    aceptaPrivacidad: false,

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
    modoNuevoEncargado: false,
    nuevoEncargado: { primer_nombre: '', segundo_nombre: '', primer_apellido: '', segundo_apellido: '', cargo: '', telefono: '', email: '' },
    // Debe coincidir con Modules\Core\Models\Encargado::CARGOS en la intranet
    cargosDisponibles: [
      'Coordinador/a',
      'Enfermera/o Coordinadora/o',
      'Encargado/a de Equipos',
      'Técnico/a en Enfermería',
      'Administrador/a',
    ],

    // Página 2
    todosEquipos: [],
    equipos: [],
    imagenes: [],         // File objects
    imagenesPreview: [],  // { id, url, name }

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
      this.cancelarNuevoEncargado();
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

    activarNuevoEncargado() {
      this.modoNuevoEncargado = true;
      this.encargadoId = null;
      this.nombreInput = '';
      this.encargadosSugeridos = [];
      this.mostrarEncargados = false;
    },

    cancelarNuevoEncargado() {
      this.modoNuevoEncargado = false;
      this.nuevoEncargado = { primer_nombre: '', segundo_nombre: '', primer_apellido: '', segundo_apellido: '', cargo: '', telefono: '', email: '' };
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

    equiposPorTipo(tipo, input, idx) {
      if (!tipo) return [];
      const yaSeleccionados = new Set(
        this.equipos.filter((_, j) => j !== idx).map(e => e.equipo_id).filter(id => id !== null)
      );
      let lista = this.todosEquipos.filter(e => e.tipo === tipo && !yaSeleccionados.has(e.id));
      if (input && input.trim().length >= 1) {
        const q = input.toLowerCase();
        lista = lista.filter(e => e.label.toLowerCase().includes(q));
      }
      return lista.slice(0, 8);
    },

    seleccionarEquipo(i, sug) {
      if (this.equipos.some((e, j) => j !== i && e.equipo_id === sug.id)) return;
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

    // ── Imágenes ──────────────────────────────────────────────────────────
    agregarImagenes(event) {
      const files = Array.from(event.target.files);
      const permitidos = ['image/jpeg', 'image/png', 'image/webp'];
      const maxSize = 5 * 1024 * 1024; // 5 MB

      for (const file of files) {
        if (this.imagenes.length >= 3) break;
        if (!permitidos.includes(file.type)) {
          this.errorMsg = `"${file.name}" no es un formato válido. Solo JPG, PNG o WEBP.`;
          continue;
        }
        if (file.size > maxSize) {
          this.errorMsg = `"${file.name}" excede los 5 MB.`;
          continue;
        }
        const id = Date.now() + '-' + Math.random().toString(36).slice(2);
        this.imagenes.push({ id, file });
        this.imagenesPreview.push({ id, url: URL.createObjectURL(file), name: file.name });
      }
      // Reset el input para permitir re-seleccionar el mismo archivo
      event.target.value = '';
    },

    quitarImagen(idx) {
      const removed = this.imagenesPreview[idx];
      if (removed) URL.revokeObjectURL(removed.url);
      this.imagenesPreview.splice(idx, 1);
      this.imagenes.splice(idx, 1);
    },

    // ── Validación ────────────────────────────────────────────────────────
    erroresPagina1() {
      if (!this.centroId) return ['Selecciona tu clínica o centro médico.'];

      if (this.modoNuevoEncargado) {
        const errores = [];
        const ne = this.nuevoEncargado;
        if (!ne.primer_nombre.trim())                              errores.push('ingresa tu nombre');
        if (!ne.primer_apellido.trim())                             errores.push('ingresa tu apellido');
        if (!ne.segundo_apellido.trim())                            errores.push('ingresa tu segundo apellido');
        if (!ne.cargo)                                              errores.push('selecciona tu cargo');
        if (!/^\d{9}$/.test(ne.telefono))                           errores.push('ingresa un teléfono válido de 9 dígitos');
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(ne.email.trim()))    errores.push('ingresa un email válido');
        return errores.length ? ['Antes de continuar debes ' + errores.join(', ') + '.'] : [];
      }

      if (!this.encargadoId) return ['Selecciona tu nombre en el listado, o regístrate si no apareces.'];
      return [];
    },

    erroresPagina2() {
      if (this.equipos.length === 0) return ['Agrega al menos un equipo.'];
      const errores = [];
      this.equipos.forEach((eq, i) => {
        const n = i + 1;
        if (!eq.tipo)                              { errores.push(`Equipo ${n}: selecciona el tipo de equipo.`); return; }
        if (!eq.equipo_id)                         { errores.push(`Equipo ${n}: selecciona el equipo.`); return; }
        if (eq.tieneAbierto)                       { errores.push(`Equipo ${n}: ya tiene un llamado abierto.`); return; }
        if (eq.falla.trim().length < 5)              errores.push(`Equipo ${n}: describe la falla (mínimo 5 caracteres).`);
        if (!eq.momento)                              errores.push(`Equipo ${n}: indica el momento en que se presentó la falla.`);
        if (eq.operativo === null)                    errores.push(`Equipo ${n}: indica si el equipo está operativo.`);
      });
      return errores;
    },

    // ── Navegación ────────────────────────────────────────────────────────
    siguiente() {
      const errores = this.erroresPagina1();
      if (errores.length) {
        this.errorMsg = errores[0];
        return;
      }
      this.errorMsg = '';
      if (this.todosEquipos.length === 0 && this.centroId) this.cargarEquipos();
      this.pagina = 2;
      this.$nextTick(() => {
        const scroll = document.querySelector('.tq-form-scroll');
        if (scroll) scroll.scrollTop = 0;
      });
    },

    // ── Envío ─────────────────────────────────────────────────────────────
    async enviar() {
      if (this.enviando) return;
      const errores = this.erroresPagina2();
      if (errores.length) {
        this.errorMsg = 'Antes de enviar debes completar lo siguiente: ' + errores.join(' ');
        return;
      }
      if (!this.aceptaPrivacidad) {
        this.errorMsg = 'Debes aceptar la Política de Privacidad para enviar el llamado.';
        return;
      }
      this.enviando = true;
      this.errorMsg = '';
      try {
        const formData = new FormData();

        // Datos del llamado como JSON en un campo "data"
        formData.append('data', JSON.stringify({
          csrf_token: this.csrfToken,
          hp: this.hp,
          centro_id: this.centroId,
          encargado_id: this.modoNuevoEncargado ? null : this.encargadoId,
          encargado_nuevo: this.modoNuevoEncargado ? { ...this.nuevoEncargado } : null,
          equipos: this.equipos.map(eq => ({
            equipo_id:          eq.equipo_id,
            operativo:          eq.operativo,
            descripcion_falla:  eq.falla.trim(),
            momento:            eq.momento || null,
            comentarios_extra:  eq.comentarios.trim() || null,
          }))
        }));

        // Imágenes como archivos
        for (const img of this.imagenes) {
          formData.append('imagenes[]', img.file);
        }

        const r = await fetch('process/procesar_llamado.php', {
          method: 'POST',
          body: formData,
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
