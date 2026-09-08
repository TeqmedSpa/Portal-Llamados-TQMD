<?php
require_once __DIR__ . '/bootstrap.php';
$pageTitle = 'Política de Privacidad — Teqmed';
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<style>
.pp-root {
  max-width: 720px;
  margin: 0 auto;
  padding: 40px 24px 80px;
  color: var(--tq-ink);
  line-height: 1.75;
}
.pp-back {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-family: 'Manrope', sans-serif;
  font-size: 13px;
  font-weight: 600;
  color: var(--tq-muted);
  text-decoration: none;
  margin-bottom: 28px;
  transition: color 0.15s;
}
.pp-back:hover { color: var(--tq-teal-800); }
.pp-header {
  margin-bottom: 36px;
  padding-bottom: 24px;
  border-bottom: 2px solid var(--tq-teal-800);
}
.pp-logo {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
}
.pp-logo img { height: 40px; width: auto; }
.pp-logo span {
  font-family: 'Manrope', sans-serif;
  font-weight: 800;
  font-size: 18px;
  color: var(--tq-teal-900);
  letter-spacing: -0.01em;
}
.pp-title {
  font-family: 'Manrope', sans-serif;
  font-size: 26px;
  font-weight: 800;
  color: var(--tq-ink);
  line-height: 1.2;
  margin: 0 0 8px;
}
.pp-subtitle {
  font-size: 14px;
  color: var(--tq-ink-2);
}
.pp-meta {
  margin-top: 12px;
  font-family: 'Manrope', sans-serif;
  font-size: 12px;
  color: var(--tq-muted);
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

/* TOC */
.pp-toc {
  background: var(--tq-surface-2);
  border: 1px solid var(--tq-border-soft);
  border-radius: 10px;
  padding: 20px 24px;
  margin-bottom: 36px;
}
.pp-toc-title {
  font-family: 'Manrope', sans-serif;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--tq-muted);
  margin-bottom: 12px;
}
.pp-toc ol {
  list-style: none;
  padding: 0;
  margin: 0;
  counter-reset: pp-toc;
}
.pp-toc li {
  counter-increment: pp-toc;
  margin-bottom: 5px;
}
.pp-toc li::before {
  content: counter(pp-toc) ".";
  font-family: 'Manrope', sans-serif;
  font-weight: 700;
  color: var(--tq-teal-700);
  display: inline-block;
  width: 22px;
  font-size: 12px;
}
.pp-toc a {
  font-family: 'Manrope', sans-serif;
  font-size: 13px;
  color: var(--tq-ink-2);
  text-decoration: none;
}
.pp-toc a:hover { color: var(--tq-teal-800); text-decoration: underline; }

/* Articles */
.pp-article {
  margin-bottom: 32px;
  scroll-margin-top: 20px;
}
.pp-art-header {
  display: flex;
  align-items: baseline;
  gap: 10px;
  margin-bottom: 10px;
  padding-bottom: 6px;
  border-bottom: 1px solid var(--tq-border-soft);
}
.pp-art-num {
  font-family: 'Manrope', sans-serif;
  font-size: 12px;
  font-weight: 800;
  color: var(--tq-teal-700);
  white-space: nowrap;
}
.pp-art-title {
  font-family: 'Manrope', sans-serif;
  font-size: 16px;
  font-weight: 700;
  color: var(--tq-ink);
  margin: 0;
}
.pp-article p {
  margin: 0 0 10px;
  font-size: 14px;
  text-align: justify;
  hyphens: auto;
}
.pp-article p:last-child { margin-bottom: 0; }
.pp-article ul, .pp-article ol {
  padding-left: 20px;
  margin: 8px 0 12px;
}
.pp-article li {
  font-size: 14px;
  margin-bottom: 6px;
  padding-left: 2px;
}
.pp-article li::marker { color: var(--tq-muted); }
.pp-article strong { color: var(--tq-ink); }

/* Highlight box */
.pp-highlight {
  background: #eaf4f8;
  border: 1px solid #b0cede;
  border-radius: 8px;
  padding: 14px 18px;
  margin: 12px 0;
  font-size: 13px;
  line-height: 1.6;
}
.pp-highlight strong {
  font-family: 'Manrope', sans-serif;
  font-weight: 700;
}

/* Data table */
.pp-table-wrap { overflow-x: auto; margin: 12px 0 16px; }
.pp-table {
  width: 100%;
  border-collapse: collapse;
  font-family: 'Manrope', sans-serif;
  font-size: 12px;
}
.pp-table thead { background: var(--tq-surface-2); }
.pp-table th {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: var(--tq-muted);
  text-align: left;
  padding: 8px 12px;
  border-bottom: 1px solid var(--tq-border);
  white-space: nowrap;
}
.pp-table td {
  padding: 8px 12px;
  border-bottom: 1px solid var(--tq-border-soft);
  color: var(--tq-ink-2);
  vertical-align: top;
  line-height: 1.5;
}
.pp-table tr:last-child td { border-bottom: none; }
.pp-table td:first-child { color: var(--tq-ink); font-weight: 600; }

/* Footer */
.pp-footer {
  margin-top: 40px;
  padding-top: 20px;
  border-top: 2px solid var(--tq-teal-800);
  font-family: 'Manrope', sans-serif;
  font-size: 11px;
  color: var(--tq-muted);
  line-height: 1.6;
}

@media (max-width: 600px) {
  .pp-root { padding: 24px 16px 60px; }
  .pp-title { font-size: 20px; }
  .pp-art-header { flex-direction: column; gap: 2px; }
  .pp-article p { text-align: left; }
}

@media print {
  .pp-back { display: none; }
  .pp-root { max-width: none; padding: 0; }
}
</style>

<div class="pp-root">

  <a href="/" class="pp-back">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M19 12H5m0 0l7 7m-7-7l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    Volver al formulario
  </a>

  <header class="pp-header">
    <div class="pp-logo">
      <img src="/assets/images/logo.png" alt="TEQMED">
      <span>TEQMED SpA</span>
    </div>
    <h1 class="pp-title">Politica de Privacidad</h1>
    <p class="pp-subtitle">Portal de Soporte Tecnico — llamados.teqmed.cl</p>
    <div class="pp-meta">
      <span>Ultima actualizacion: 1 de septiembre de 2026</span>
      <span>Version 1.0</span>
    </div>
  </header>

  <nav class="pp-toc">
    <div class="pp-toc-title">Contenido</div>
    <ol>
      <li><a href="#art1">Responsable del tratamiento</a></li>
      <li><a href="#art2">Ambito de aplicacion</a></li>
      <li><a href="#art3">Datos personales que recopilamos</a></li>
      <li><a href="#art4">Finalidad del tratamiento</a></li>
      <li><a href="#art5">Base legal del tratamiento</a></li>
      <li><a href="#art6">Destinatarios de los datos</a></li>
      <li><a href="#art7">Transferencia internacional de datos</a></li>
      <li><a href="#art8">Plazos de conservacion</a></li>
      <li><a href="#art9">Derechos de los titulares</a></li>
      <li><a href="#art10">Ejercicio de derechos</a></li>
      <li><a href="#art11">Cookies y tecnologias similares</a></li>
      <li><a href="#art12">Seguridad de los datos</a></li>
      <li><a href="#art13">Modificaciones a esta politica</a></li>
      <li><a href="#art14">Legislacion aplicable</a></li>
    </ol>
  </nav>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art1">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 1</span>
      <h2 class="pp-art-title">Responsable del tratamiento</h2>
    </div>
    <p>El responsable del tratamiento de los datos personales recopilados a traves de este portal es:</p>
    <div class="pp-highlight">
      <strong>TEQMED SpA</strong><br>
      Castellon 970, Concepcion, Region del Biobio, Chile<br>
      Correo electronico: <strong>contacto@teqmed.cl</strong><br>
      Sitio web: teqmed.cl
    </div>
    <p>TEQMED SpA (en adelante "TEQMED" o "nosotros") es una empresa dedicada a la provision de servicios de mantenimiento, reparacion y soporte tecnico de equipos medicos instalados en centros de dialisis y establecimientos de salud a lo largo de Chile.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art2">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 2</span>
      <h2 class="pp-art-title">Ambito de aplicacion</h2>
    </div>
    <p>Esta politica de privacidad se aplica al tratamiento de datos personales realizado a traves del <strong>Portal de Soporte Tecnico</strong> de TEQMED, accesible en <strong>llamados.teqmed.cl</strong> (en adelante "el Portal"), mediante el cual el personal autorizado de nuestros clientes puede crear y gestionar solicitudes de soporte tecnico ("llamados").</p>
    <p>Esta politica tambien aplica a los datos personales que, habiendo sido recopilados a traves del Portal, sean posteriormente tratados en nuestros sistemas internos de gestion para dar cumplimiento al servicio contratado.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art3">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 3</span>
      <h2 class="pp-art-title">Datos personales que recopilamos</h2>
    </div>
    <p>Recopilamos las siguientes categorias de datos personales:</p>

    <div class="pp-table-wrap">
      <table class="pp-table">
        <thead>
          <tr>
            <th>Categoria</th>
            <th>Datos especificos</th>
            <th>Fuente</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Datos de identificacion</td>
            <td>Nombre, apellidos, cargo</td>
            <td>Proporcionados por el usuario al registrarse como contacto</td>
          </tr>
          <tr>
            <td>Datos de contacto</td>
            <td>Numero de telefono, direccion de correo electronico</td>
            <td>Proporcionados por el usuario al registrarse como contacto</td>
          </tr>
          <tr>
            <td>Datos de navegacion</td>
            <td>Direccion IP, identificador de sesion</td>
            <td>Recopilados automaticamente al acceder al Portal</td>
          </tr>
          <tr>
            <td>Contenido generado</td>
            <td>Descripciones de fallas, fotografias adjuntas a los llamados</td>
            <td>Proporcionados voluntariamente por el usuario al crear un llamado</td>
          </tr>
        </tbody>
      </table>
    </div>

    <p>Las fotografias adjuntas podrian contener, de manera incidental, imagenes de personas o informacion identificable visible en el entorno. Le solicitamos que, en la medida de lo posible, evite incluir datos personales de terceros en las fotografias.</p>
    <p>TEQMED <strong>no recopila datos sensibles</strong> (datos de salud de pacientes, origen etnico, creencias religiosas, datos biometricos u otros contemplados en el articulo 16 bis de la Ley 19.628 modificada).</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art4">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 4</span>
      <h2 class="pp-art-title">Finalidad del tratamiento</h2>
    </div>
    <p>Los datos personales son tratados exclusivamente para las siguientes finalidades:</p>
    <ol>
      <li><strong>Gestion de llamados de soporte tecnico:</strong> recibir, registrar, asignar, dar seguimiento y resolver las solicitudes de soporte tecnico reportadas a traves del Portal.</li>
      <li><strong>Comunicacion con el usuario:</strong> enviar notificaciones por correo electronico (y, cuando este habilitado, por WhatsApp) sobre el estado de los llamados, la asignacion de tecnicos y la resolucion del servicio.</li>
      <li><strong>Identificacion y autenticacion:</strong> verificar la identidad del contacto autorizado y asociarlo al centro medico correspondiente.</li>
      <li><strong>Generacion de informes tecnicos:</strong> los datos del contacto que reporta una falla pueden vincularse a informes correctivos generados como resultado del llamado, los cuales forman parte de la documentacion tecnica del servicio.</li>
      <li><strong>Seguridad del sistema:</strong> proteger el Portal contra accesos no autorizados, ataques automatizados y usos abusivos mediante el registro temporal de direcciones IP.</li>
    </ol>
    <p>TEQMED <strong>no utiliza los datos personales</strong> recopilados a traves del Portal para fines de marketing, elaboracion de perfiles, toma de decisiones automatizadas, ni los comparte con terceros para fines comerciales.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art5">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 5</span>
      <h2 class="pp-art-title">Base legal del tratamiento</h2>
    </div>
    <p>El tratamiento de datos personales se sustenta en las siguientes bases legales, conforme al articulo 13 de la Ley 19.628 modificada por la Ley 21.719:</p>
    <ul>
      <li><strong>Ejecucion de un contrato</strong> (Art. 13 letra c): el tratamiento de datos de los contactos de clientes es necesario para la ejecucion del contrato de prestacion de servicios de mantenimiento celebrado entre TEQMED y la institucion a la que pertenece el usuario. La gestion de llamados de soporte es una obligacion contractual derivada de dicho contrato.</li>
      <li><strong>Consentimiento del titular</strong> (Art. 13 letra a): cuando un usuario se registra como nuevo contacto a traves del Portal, su consentimiento explicito es requerido antes de que sus datos sean almacenados. Este consentimiento es libre, informado, especifico e inequivoco.</li>
      <li><strong>Interes legitimo</strong> (Art. 13 letra e): el registro temporal de direcciones IP para la proteccion del Portal contra ataques y abusos se sustenta en el interes legitimo de TEQMED en mantener la seguridad de sus sistemas, sin que ello prevalezca sobre los derechos fundamentales del titular.</li>
    </ul>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art6">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 6</span>
      <h2 class="pp-art-title">Destinatarios de los datos</h2>
    </div>
    <p>Los datos personales pueden ser compartidos con las siguientes categorias de destinatarios, exclusivamente en la medida necesaria para cumplir las finalidades descritas:</p>
    <ul>
      <li><strong>Personal interno de TEQMED:</strong> tecnicos y administradores que acceden a los datos a traves del sistema de gestion interno para la asignacion y resolucion de llamados.</li>
      <li><strong>Proveedor de alojamiento web:</strong> nuestro proveedor de hosting procesa los datos al alojar el Portal y gestionar el envio de correos electronicos. Opera como encargado del tratamiento.</li>
      <li><strong>Proveedor de almacenamiento en la nube (Cloudflare):</strong> los informes tecnicos firmados, que pueden contener nombres de contactos, son respaldados en Cloudflare R2. Cloudflare actua como encargado del tratamiento bajo su Acuerdo de Procesamiento de Datos (DPA).</li>
      <li><strong>Proveedor de mensajeria (Meta — WhatsApp Business API):</strong> cuando las notificaciones por WhatsApp estan habilitadas, el numero de telefono y nombre del contacto son transmitidos a Meta para el envio de mensajes. Meta opera bajo sus propios terminos de servicio y su DPA.</li>
    </ul>
    <p>TEQMED <strong>no vende, alquila ni cede</strong> datos personales a terceros para fines ajenos a los descritos en esta politica.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art7">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 7</span>
      <h2 class="pp-art-title">Transferencia internacional de datos</h2>
    </div>
    <p>Algunos de los proveedores mencionados en el articulo anterior almacenan o procesan datos fuera de Chile:</p>
    <ul>
      <li><strong>Cloudflare, Inc.</strong> (Estados Unidos): los respaldos de informes tecnicos en formato PDF son almacenados en infraestructura de Cloudflare R2. Cloudflare cumple con clausulas contractuales tipo y mantiene un DPA conforme a estandares internacionales de proteccion de datos.</li>
      <li><strong>Meta Platforms, Inc.</strong> (Estados Unidos): cuando las notificaciones por WhatsApp estan habilitadas, los datos de contacto son procesados por Meta. Meta dispone de un DPA y mecanismos de transferencia internacional.</li>
    </ul>
    <p>Estas transferencias se realizan conforme al articulo 16 de la Ley 19.628 modificada, asegurandonos de que los destinatarios ofrezcan niveles de proteccion adecuados a traves de clausulas contractuales u otros mecanismos reconocidos.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art8">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 8</span>
      <h2 class="pp-art-title">Plazos de conservacion</h2>
    </div>
    <p>Los datos personales son conservados unicamente durante el tiempo necesario para cumplir con las finalidades para las que fueron recopilados:</p>

    <div class="pp-table-wrap">
      <table class="pp-table">
        <thead>
          <tr>
            <th>Datos</th>
            <th>Plazo de conservacion</th>
            <th>Criterio</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Datos de identificacion y contacto</td>
            <td>Mientras exista relacion contractual vigente con el cliente</td>
            <td>Necesidad contractual</td>
          </tr>
          <tr>
            <td>Llamados y su contenido</td>
            <td>5 anos desde la resolucion</td>
            <td>Trazabilidad tecnica y obligaciones regulatorias</td>
          </tr>
          <tr>
            <td>Informes tecnicos vinculados</td>
            <td>Indefinido mientras sean requeridos por normativa sectorial</td>
            <td>Obligacion legal del sector equipamiento medico</td>
          </tr>
          <tr>
            <td>Direcciones IP (rate limiting)</td>
            <td>24 horas</td>
            <td>Seguridad del sistema</td>
          </tr>
          <tr>
            <td>Registros de auditoria</td>
            <td>24 meses</td>
            <td>Trazabilidad y seguridad</td>
          </tr>
        </tbody>
      </table>
    </div>

    <p>Una vez cumplidos los plazos indicados, los datos seran eliminados o anonimizados de forma irreversible, salvo que exista una obligacion legal que requiera su conservacion por un periodo mayor.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art9">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 9</span>
      <h2 class="pp-art-title">Derechos de los titulares</h2>
    </div>
    <p>De conformidad con la Ley 19.628 modificada por la Ley 21.719, usted tiene derecho a:</p>
    <ul>
      <li><strong>Derecho de acceso</strong> (Art. 5): solicitar confirmacion de si sus datos personales estan siendo tratados y, en caso afirmativo, obtener una copia de los mismos y la informacion sobre su tratamiento.</li>
      <li><strong>Derecho de rectificacion</strong> (Art. 6): solicitar la correccion de datos personales inexactos o incompletos.</li>
      <li><strong>Derecho de supresion</strong> (Art. 7): solicitar la eliminacion de sus datos personales cuando ya no sean necesarios para la finalidad para la que fueron recopilados, cuando retire su consentimiento, o cuando el tratamiento sea ilicito.</li>
      <li><strong>Derecho de oposicion</strong> (Art. 8 bis): oponerse al tratamiento de sus datos personales en determinadas circunstancias, incluido el tratamiento basado en interes legitimo.</li>
      <li><strong>Derecho a la portabilidad</strong> (Art. 9): solicitar la entrega de sus datos personales en un formato estructurado, de uso comun y lectura mecanica, o su transmision directa a otro responsable cuando sea tecnicamente posible.</li>
    </ul>

    <div class="pp-highlight">
      <strong>Limitaciones:</strong> el ejercicio del derecho de supresion puede estar limitado cuando los datos sean necesarios para el cumplimiento de obligaciones legales, la formulacion o defensa de reclamaciones, o la integridad de informes tecnicos ya firmados y respaldados conforme a la normativa sectorial.
    </div>

    <p>TEQMED respondera a las solicitudes de ejercicio de derechos dentro de un plazo maximo de <strong>15 dias habiles</strong> contados desde la recepcion de la solicitud, conforme a lo establecido en la ley. Este plazo podra extenderse por un periodo igual en caso de solicitudes complejas o multiples, previa comunicacion al titular.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art10">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 10</span>
      <h2 class="pp-art-title">Ejercicio de derechos</h2>
    </div>
    <p>Para ejercer cualquiera de los derechos descritos en el articulo anterior, puede contactarnos a traves de:</p>
    <div class="pp-highlight">
      <strong>Correo electronico:</strong> contacto@teqmed.cl<br>
      <strong>Asunto sugerido:</strong> "Solicitud de derechos de datos personales"<br>
      <strong>Direccion postal:</strong> TEQMED SpA, Castellon 970, Concepcion, Region del Biobio, Chile
    </div>
    <p>Para procesar su solicitud, le pediremos que se identifique adecuadamente y especifique el derecho que desea ejercer. TEQMED podra solicitar informacion adicional cuando sea necesario para verificar su identidad y evitar el acceso no autorizado a datos de terceros.</p>
    <p>Si considera que el tratamiento de sus datos personales infringe la normativa vigente, tiene derecho a presentar una reclamacion ante la <strong>Agencia de Proteccion de Datos Personales</strong> de Chile.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art11">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 11</span>
      <h2 class="pp-art-title">Cookies y tecnologias similares</h2>
    </div>
    <p>El Portal utiliza las siguientes tecnologias:</p>
    <ul>
      <li><strong>Cookie de sesion:</strong> una cookie tecnica estrictamente necesaria para el funcionamiento del Portal. Almacena un identificador de sesion anonimo que permite mantener su sesion activa mientras navega. Se configura con los atributos <em>Secure</em>, <em>HttpOnly</em> y <em>SameSite=Lax</em> para su proteccion. Se elimina automaticamente al cerrar el navegador o al expirar la sesion.</li>
      <li><strong>Token CSRF:</strong> un token de seguridad almacenado en la sesion del servidor que protege contra ataques de falsificacion de solicitudes. No contiene datos personales.</li>
    </ul>
    <p>El Portal <strong>no utiliza cookies de terceros</strong>, cookies de seguimiento, cookies publicitarias ni herramientas de analitica web. No se realizan perfiles de navegacion ni seguimiento del comportamiento del usuario.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art12">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 12</span>
      <h2 class="pp-art-title">Seguridad de los datos</h2>
    </div>
    <p>TEQMED implementa medidas tecnicas y organizativas apropiadas para proteger los datos personales contra el acceso no autorizado, la alteracion, la divulgacion o la destruccion, conforme al articulo 14 quinquies de la ley. Entre ellas:</p>
    <ul>
      <li>Cifrado de todas las comunicaciones mediante protocolo HTTPS/TLS, con cabecera HSTS habilitada.</li>
      <li>Almacenamiento de contrasenas con algoritmo de hash seguro (bcrypt).</li>
      <li>Proteccion contra ataques CSRF, inyeccion de codigo y falsificacion de solicitudes.</li>
      <li>Limitacion de tasa de acceso (rate limiting) para prevenir ataques automatizados.</li>
      <li>Validacion de tipo MIME real en archivos subidos.</li>
      <li>Cabeceras de seguridad HTTP: Content-Security-Policy, X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy.</li>
      <li>Control de acceso basado en roles con principio de privilegio minimo.</li>
      <li>Registro de auditoria de operaciones sensibles.</li>
    </ul>
    <p>Ningun sistema de transmision o almacenamiento de datos puede garantizar una seguridad absoluta. En caso de producirse una vulneracion de seguridad que pueda afectar sus datos personales, TEQMED le notificara conforme a lo dispuesto en la legislacion vigente.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art13">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 13</span>
      <h2 class="pp-art-title">Modificaciones a esta politica</h2>
    </div>
    <p>TEQMED se reserva el derecho de modificar esta politica de privacidad en cualquier momento para adaptarla a cambios normativos, jurisprudenciales o de nuestras practicas de tratamiento de datos.</p>
    <p>Cualquier modificacion sera publicada en esta misma pagina con la fecha de actualizacion correspondiente. En caso de cambios sustanciales que afecten la forma en que tratamos sus datos personales, le notificaremos a traves del correo electronico registrado en nuestro sistema, con al menos <strong>30 dias de anticipacion</strong> a la entrada en vigencia de los cambios.</p>
    <p>El uso continuado del Portal despues de la entrada en vigencia de las modificaciones constituye la aceptacion de la politica actualizada.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art14">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 14</span>
      <h2 class="pp-art-title">Legislacion aplicable</h2>
    </div>
    <p>Esta politica de privacidad se rige por la legislacion chilena, en particular:</p>
    <ul>
      <li>Ley 19.628 sobre Proteccion de la Vida Privada, modificada por la <strong>Ley 21.719</strong> que establece normas sobre proteccion de los datos personales.</li>
      <li>Las normas complementarias y reglamentos que emita la Agencia de Proteccion de Datos Personales en ejercicio de sus atribuciones.</li>
    </ul>
    <p>Para cualquier controversia derivada del tratamiento de datos personales, seran competentes los tribunales ordinarios de justicia de la ciudad de Concepcion, Region del Biobio, Chile, sin perjuicio de las competencias de la Agencia de Proteccion de Datos Personales.</p>
  </div>

  <footer class="pp-footer">
    <p><strong>TEQMED SpA</strong> — Castellon 970, Concepcion, Region del Biobio, Chile</p>
    <p>Contacto: contacto@teqmed.cl</p>
    <p style="margin-top:8px">Este documento fue redactado en cumplimiento de la Ley 21.719 y la Ley 19.628 modificada. Se recomienda su revision periodica por asesoria legal especializada.</p>
  </footer>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
