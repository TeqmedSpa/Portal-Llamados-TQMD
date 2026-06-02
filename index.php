<?php
require_once __DIR__ . '/bootstrap.php';
$pageTitle = 'Registrar Llamado — TEQMED';
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<div x-data="portalLlamado()" x-init="init()">

    <!-- Título -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Registrar llamado de servicio</h1>
        <p class="text-gray-500 mt-1 text-sm">Complete el formulario para solicitar atención técnica de TEQMED.</p>
    </div>

    <!-- Indicador de pasos -->
    <div class="flex items-center gap-3 mb-8">
        <template x-for="(label, i) in ['Identificación', 'Equipos', 'Detalle']" :key="i">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold step-dot"
                     :class="paso > i + 1 ? 'bg-tq-green text-white' : (paso === i + 1 ? 'bg-tq-blue text-white' : 'bg-gray-200 text-gray-500')">
                    <template x-if="paso > i + 1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </template>
                    <template x-if="paso <= i + 1">
                        <span x-text="i + 1"></span>
                    </template>
                </div>
                <span class="text-sm hidden sm:inline"
                      :class="paso === i + 1 ? 'font-semibold text-gray-800' : 'text-gray-400'" x-text="label"></span>
                <div x-show="i < 2" class="w-8 h-px bg-gray-300"></div>
            </div>
        </template>
    </div>

    <!-- ───────────────────────────────────────── PASO 1: Identificación -->
    <div x-show="paso === 1" class="fade-in">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-5">¿Quién realiza el llamado?</h2>

            <!-- Buscador -->
            <div class="relative mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Nombre del encargado <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input
                        type="text"
                        x-model="busqueda"
                        @input.debounce.350ms="buscarContactos()"
                        @focus="mostrarDropdown = sugerencias.length > 0"
                        @click.outside="mostrarDropdown = false"
                        placeholder="Escriba su nombre o apellido…"
                        autocomplete="off"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-tq-blue focus:border-transparent transition"
                        :class="{'error': errores.contacto}">
                    <div x-show="buscando" class="absolute right-3 top-3">
                        <div class="spinner"></div>
                    </div>
                </div>
                <p x-show="errores.contacto" class="error-msg" x-text="errores.contacto"></p>

                <!-- Dropdown sugerencias -->
                <div x-show="mostrarDropdown && sugerencias.length > 0"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                    <template x-for="s in sugerencias" :key="s.id">
                        <button type="button"
                                @click="seleccionarContacto(s)"
                                class="w-full text-left px-4 py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors autocomplete-item">
                            <p class="text-sm font-semibold text-gray-900" x-text="s.nombre + ' ' + s.apellido"></p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                <span x-text="s.cargo || 'Sin cargo'"></span>
                                <span class="mx-1 text-gray-300">·</span>
                                <span x-text="s.centro_nombre"></span>
                            </p>
                        </button>
                    </template>
                </div>

                <div x-show="mostrarDropdown && sugerencias.length === 0 && !buscando && busqueda.length >= 2"
                     class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow px-4 py-3">
                    <p class="text-sm text-gray-500">No se encontraron encargados con ese nombre.</p>
                </div>
            </div>

            <!-- Datos del contacto seleccionado -->
            <div x-show="contacto" x-transition class="bg-tq-blue-light border border-blue-100 rounded-lg p-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">Cargo</p>
                    <p class="text-sm font-medium text-gray-800" x-text="contacto?.cargo || '—'"></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">Teléfono</p>
                    <p class="text-sm font-medium text-gray-800" x-text="contacto?.telefono || '—'"></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">Centro médico</p>
                    <p class="text-sm font-medium text-gray-800" x-text="contacto?.centro_nombre || '—'"></p>
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-5">
            <button type="button" @click="irPaso2()"
                    class="px-6 py-2.5 bg-tq-blue text-white font-semibold rounded-lg hover:bg-tq-blue-dark transition-colors text-sm">
                Continuar →
            </button>
        </div>
    </div>

    <!-- ───────────────────────────────────────── PASO 2: Equipos -->
    <div x-show="paso === 2" class="fade-in">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-semibold text-gray-800">¿Qué equipos presentan problemas?</h2>
                <button type="button" @click="agregarEquipo()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-tq-blue border border-tq-blue rounded-lg hover:bg-tq-blue-light transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Agregar equipo
                </button>
            </div>

            <p x-show="errores.equipos" class="error-msg mb-3" x-text="errores.equipos"></p>

            <!-- Estado vacío -->
            <div x-show="equiposForm.length === 0"
                 class="text-center py-10 text-sm text-gray-400 border-2 border-dashed border-gray-200 rounded-xl">
                Haga clic en "Agregar equipo" para seleccionar los equipos con problemas.
            </div>

            <!-- Lista de equipos -->
            <div class="space-y-4">
                <template x-for="(eq, i) in equiposForm" :key="i">
                    <div class="border border-gray-200 rounded-xl p-4 relative fade-in">

                        <!-- Quitar equipo -->
                        <button type="button" @click="quitarEquipo(i)"
                                class="absolute top-3 right-3 text-gray-300 hover:text-red-400 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3"
                           x-text="'Equipo ' + (i + 1)"></p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <!-- Selector de equipo -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Equipo <span class="text-red-500">*</span>
                                </label>
                                <select x-model="eq.equipo_id"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-tq-blue focus:border-transparent transition"
                                        :class="{'error': eq.error_equipo}">
                                    <option value="">— Seleccione un equipo —</option>
                                    <template x-for="e in equiposDisponibles" :key="e.id">
                                        <option :value="e.id" x-text="e.label"></option>
                                    </template>
                                </select>
                                <p x-show="eq.error_equipo" class="error-msg" x-text="eq.error_equipo"></p>
                            </div>

                            <!-- Toggle operativo -->
                            <div class="flex items-end pb-1">
                                <label class="flex items-center gap-3 cursor-pointer select-none">
                                    <div class="relative" @click="eq.operativo = !eq.operativo">
                                        <div class="w-12 h-6 rounded-full toggle-track"
                                             :class="eq.operativo ? 'bg-tq-green' : 'bg-red-400'"></div>
                                        <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow toggle-thumb"
                                             :class="eq.operativo ? 'translate-x-6' : 'translate-x-0'"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700"
                                          x-text="eq.operativo ? 'Operativo' : 'Fuera de servicio'"></span>
                                </label>
                            </div>
                        </div>

                        <!-- Descripción del problema -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Descripción del problema <span class="text-red-500">*</span>
                            </label>
                            <textarea x-model="eq.descripcion_problema"
                                      rows="2"
                                      placeholder="Describa el problema observado en este equipo…"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-tq-blue focus:border-transparent transition resize-none"
                                      :class="{'error': eq.error_descripcion}"></textarea>
                            <p x-show="eq.error_descripcion" class="error-msg" x-text="eq.error_descripcion"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="flex justify-between mt-5">
            <button type="button" @click="paso = 1"
                    class="px-5 py-2.5 text-sm font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                ← Volver
            </button>
            <button type="button" @click="irPaso3()"
                    class="px-6 py-2.5 bg-tq-blue text-white font-semibold rounded-lg hover:bg-tq-blue-dark transition-colors text-sm">
                Continuar →
            </button>
        </div>
    </div>

    <!-- ───────────────────────────────────────── PASO 3: Detalle general -->
    <div x-show="paso === 3" class="fade-in">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
            <h2 class="text-base font-semibold text-gray-800">Detalle del llamado</h2>

            <!-- Resumen contacto + equipos -->
            <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700 space-y-1">
                <p><span class="font-medium">Contacto:</span> <span x-text="contacto?.nombre + ' ' + contacto?.apellido"></span></p>
                <p><span class="font-medium">Centro médico:</span> <span x-text="contacto?.centro_nombre"></span></p>
                <p><span class="font-medium">Equipos:</span> <span x-text="equiposForm.length + ' equipo(s) reportado(s)'"></span></p>
            </div>

            <!-- Título -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Título del llamado <span class="text-red-500">*</span>
                </label>
                <input type="text" x-model="titulo"
                       placeholder="Ej: Falla en sistema de diálisis sala 2"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-tq-blue focus:border-transparent transition"
                       :class="{'error': errores.titulo}">
                <p x-show="errores.titulo" class="error-msg" x-text="errores.titulo"></p>
            </div>

            <!-- Descripción general -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Descripción general <span class="text-red-500">*</span>
                </label>
                <textarea x-model="descripcion" rows="4"
                          placeholder="Explique el contexto del llamado: cuándo ocurrió, si hay urgencia clínica, intentos de resolución previos, etc."
                          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-tq-blue focus:border-transparent transition resize-none"
                          :class="{'error': errores.descripcion}"></textarea>
                <p x-show="errores.descripcion" class="error-msg" x-text="errores.descripcion"></p>
            </div>

            <!-- Prioridad -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Prioridad</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <template x-for="p in [{val:'baja',label:'Baja',color:'gray'},{val:'normal',label:'Normal',color:'blue'},{val:'alta',label:'Alta',color:'amber'},{val:'urgente',label:'Urgente',color:'red'}]" :key="p.val">
                        <label class="cursor-pointer">
                            <input type="radio" x-model="prioridad" :value="p.val" class="sr-only">
                            <div class="border-2 rounded-lg px-3 py-2 text-center text-sm font-medium transition-all"
                                 :class="prioridad === p.val
                                    ? (p.val === 'urgente' ? 'border-red-500 bg-red-50 text-red-700' :
                                       p.val === 'alta'    ? 'border-amber-500 bg-amber-50 text-amber-700' :
                                       p.val === 'normal'  ? 'border-tq-blue bg-tq-blue-light text-tq-blue' :
                                                             'border-gray-400 bg-gray-100 text-gray-700')
                                    : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                                 x-text="p.label">
                            </div>
                        </label>
                    </template>
                </div>
            </div>
        </div>

        <div class="flex justify-between mt-5">
            <button type="button" @click="paso = 2"
                    class="px-5 py-2.5 text-sm font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                ← Volver
            </button>
            <button type="button" @click="enviar()" :disabled="enviando"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-tq-blue text-white font-semibold rounded-lg hover:bg-tq-blue-dark transition-colors text-sm disabled:opacity-60">
                <div x-show="enviando" class="spinner !border-t-white"></div>
                <span x-text="enviando ? 'Enviando…' : 'Enviar llamado'"></span>
            </button>
        </div>
    </div>

    <!-- ───────────────────────────────────────── CONFIRMACIÓN -->
    <div x-show="paso === 4" x-transition class="text-center py-14 fade-in">
        <div class="w-16 h-16 bg-tq-green-light rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8 text-tq-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Llamado registrado</h2>
        <p class="text-gray-500 mb-3">Su solicitud fue recibida correctamente.</p>
        <p class="text-tq-blue font-mono font-bold text-lg mb-8" x-text="numeroLlamado"></p>
        <p class="text-sm text-gray-500 mb-8">El equipo técnico de TEQMED tomará contacto a la brevedad.</p>
        <button type="button" @click="reiniciar()"
                class="inline-flex items-center px-5 py-2.5 bg-tq-blue text-white font-medium rounded-lg hover:bg-tq-blue-dark transition-colors text-sm">
            Registrar otro llamado
        </button>
    </div>

    <!-- ───────────────────────────────────────── ERROR GLOBAL -->
    <div x-show="errorGlobal"
         class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-red-600 text-white text-sm font-medium px-6 py-3 rounded-xl shadow-lg z-50 fade-in"
         x-text="errorGlobal"
         x-transition></div>

</div>

<script>
function portalLlamado() {
    return {
        paso: 1,
        busqueda: '',
        buscando: false,
        sugerencias: [],
        mostrarDropdown: false,
        contacto: null,
        equiposDisponibles: [],
        equiposForm: [],
        titulo: '',
        descripcion: '',
        prioridad: 'normal',
        enviando: false,
        numeroLlamado: '',
        errorGlobal: '',
        errores: { contacto: '', equipos: '', titulo: '', descripcion: '' },

        init() {},

        async buscarContactos() {
            const q = this.busqueda.trim();
            if (q.length < 2) { this.sugerencias = []; this.mostrarDropdown = false; return; }
            this.buscando = true;
            try {
                const res = await fetch(`/process/buscar_contactos.php?q=${encodeURIComponent(q)}`);
                this.sugerencias = await res.json();
                this.mostrarDropdown = true;
            } catch { this.sugerencias = []; }
            finally { this.buscando = false; }
        },

        async seleccionarContacto(s) {
            this.contacto = s;
            this.busqueda = s.nombre + ' ' + s.apellido;
            this.mostrarDropdown = false;
            this.sugerencias = [];
            this.equiposForm = [];
            this.errores.contacto = '';
            // Cargar equipos del centro
            try {
                const res = await fetch(`/process/buscar_equipos.php?centro_id=${s.centro_medico_id}`);
                this.equiposDisponibles = await res.json();
            } catch { this.equiposDisponibles = []; }
        },

        irPaso2() {
            this.errores.contacto = '';
            if (!this.contacto) { this.errores.contacto = 'Debes seleccionar un encargado de la lista.'; return; }
            this.paso = 2;
            window.scrollTo(0, 0);
        },

        irPaso3() {
            this.errores.equipos = '';
            let valido = true;
            if (this.equiposForm.length === 0) { this.errores.equipos = 'Debes agregar al menos un equipo.'; valido = false; }
            this.equiposForm.forEach(eq => {
                eq.error_equipo = '';
                eq.error_descripcion = '';
                if (!eq.equipo_id) { eq.error_equipo = 'Selecciona un equipo.'; valido = false; }
                if ((eq.descripcion_problema || '').trim().length < 5) { eq.error_descripcion = 'Mínimo 5 caracteres.'; valido = false; }
            });
            if (!valido) return;
            this.paso = 3;
            window.scrollTo(0, 0);
        },

        agregarEquipo() {
            this.equiposForm.push({ equipo_id: '', operativo: true, descripcion_problema: '', error_equipo: '', error_descripcion: '' });
        },

        quitarEquipo(i) {
            this.equiposForm.splice(i, 1);
        },

        async enviar() {
            this.errores.titulo = '';
            this.errores.descripcion = '';
            let valido = true;
            if (this.titulo.trim().length < 5)      { this.errores.titulo      = 'Mínimo 5 caracteres.'; valido = false; }
            if (this.descripcion.trim().length < 10) { this.errores.descripcion = 'Mínimo 10 caracteres.'; valido = false; }
            if (!valido) return;

            this.enviando = true;
            this.errorGlobal = '';

            try {
                const payload = {
                    contacto_id: this.contacto.id,
                    titulo:      this.titulo.trim(),
                    descripcion: this.descripcion.trim(),
                    prioridad:   this.prioridad,
                    equipos:     this.equiposForm.map(eq => ({
                        equipo_id:           eq.equipo_id,
                        operativo:           eq.operativo,
                        descripcion_problema: eq.descripcion_problema.trim(),
                    })),
                };

                const res  = await fetch('/process/procesar_llamado.php', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body:    JSON.stringify(payload),
                });
                const data = await res.json();

                if (data.success) {
                    this.numeroLlamado = data.numero;
                    this.paso = 4;
                    window.scrollTo(0, 0);
                } else {
                    this.errorGlobal = data.message || 'Error al enviar el llamado.';
                    setTimeout(() => this.errorGlobal = '', 5000);
                }
            } catch {
                this.errorGlobal = 'Error de conexión. Intente nuevamente.';
                setTimeout(() => this.errorGlobal = '', 5000);
            } finally {
                this.enviando = false;
            }
        },

        reiniciar() {
            this.paso = 1;
            this.busqueda = '';
            this.contacto = null;
            this.equiposForm = [];
            this.equiposDisponibles = [];
            this.titulo = '';
            this.descripcion = '';
            this.prioridad = 'normal';
            this.numeroLlamado = '';
            this.errores = { contacto: '', equipos: '', titulo: '', descripcion: '' };
            window.scrollTo(0, 0);
        },
    };
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
