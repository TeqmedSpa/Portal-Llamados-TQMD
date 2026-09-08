<?php
require_once __DIR__ . '/bootstrap.php';
$pageTitle = 'Política de Privacidad — TEQMED';
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
    <h1 class="pp-title">Política de Privacidad</h1>
    <p class="pp-subtitle">Portal de Soporte Técnico — llamados.teqmed.cl</p>
    <div class="pp-meta">
      <span>Última actualización: 1 de septiembre de 2026</span>
      <span>Versión 1.0</span>
    </div>
  </header>

  <nav class="pp-toc">
    <div class="pp-toc-title">Contenido</div>
    <ol>
      <li><a href="#art1">Responsable del tratamiento</a></li>
      <li><a href="#art2">Ámbito de aplicación</a></li>
      <li><a href="#art3">Datos personales que recopilamos</a></li>
      <li><a href="#art4">Finalidad del tratamiento</a></li>
      <li><a href="#art5">Base legal del tratamiento</a></li>
      <li><a href="#art6">Destinatarios de los datos</a></li>
      <li><a href="#art7">Transferencia internacional de datos</a></li>
      <li><a href="#art8">Plazos de conservación</a></li>
      <li><a href="#art9">Derechos de los titulares</a></li>
      <li><a href="#art10">Ejercicio de derechos</a></li>
      <li><a href="#art11">Cookies y tecnologías similares</a></li>
      <li><a href="#art12">Seguridad de los datos</a></li>
      <li><a href="#art13">Modificaciones a esta política</a></li>
      <li><a href="#art14">Legislación aplicable</a></li>
    </ol>
  </nav>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art1">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 1</span>
      <h2 class="pp-art-title">Responsable del tratamiento</h2>
    </div>
    <p>El responsable del tratamiento de los datos personales recopilados a través de este portal es:</p>
    <div class="pp-highlight">
      <strong>TEQMED SpA</strong><br>
      Castellón 970, Concepción, Región del Biobío, Chile<br>
      Correo electrónico: <strong>contacto@teqmed.cl</strong><br>
      Sitio web: teqmed.cl
    </div>
    <p>TEQMED SpA (en adelante "TEQMED" o "nosotros") es una empresa dedicada a la provisión de servicios de mantenimiento, reparación y soporte técnico de equipos médicos instalados en centros de diálisis y establecimientos de salud a lo largo de Chile.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art2">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 2</span>
      <h2 class="pp-art-title">Ámbito de aplicación</h2>
    </div>
    <p>Esta política de privacidad se aplica al tratamiento de datos personales realizado a través del <strong>Portal de Soporte Técnico</strong> de TEQMED, accesible en <strong>llamados.teqmed.cl</strong> (en adelante "el Portal"), mediante el cual el personal autorizado de nuestros clientes puede crear y gestionar solicitudes de soporte técnico ("llamados").</p>
    <p>Esta política también aplica a los datos personales que, habiendo sido recopilados a través del Portal, sean posteriormente tratados en nuestros sistemas internos de gestión para dar cumplimiento al servicio contratado.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art3">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 3</span>
      <h2 class="pp-art-title">Datos personales que recopilamos</h2>
    </div>
    <p>Recopilamos las siguientes categorías de datos personales:</p>

    <div class="pp-table-wrap">
      <table class="pp-table">
        <thead>
          <tr>
            <th>Categoría</th>
            <th>Datos específicos</th>
            <th>Fuente</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Datos de identificación</td>
            <td>Nombre, apellidos, cargo</td>
            <td>Proporcionados por el usuario al registrarse como contacto</td>
          </tr>
          <tr>
            <td>Datos de contacto</td>
            <td>Número de teléfono, dirección de correo electrónico</td>
            <td>Proporcionados por el usuario al registrarse como contacto</td>
          </tr>
          <tr>
            <td>Datos de navegación</td>
            <td>Dirección IP, identificador de sesión</td>
            <td>Recopilados automáticamente al acceder al Portal</td>
          </tr>
          <tr>
            <td>Contenido generado</td>
            <td>Descripciones de fallas, fotografías adjuntas a los llamados</td>
            <td>Proporcionados voluntariamente por el usuario al crear un llamado</td>
          </tr>
        </tbody>
      </table>
    </div>

    <p>Las fotografías adjuntas podrían contener, de manera incidental, imágenes de personas o información identificable visible en el entorno. Le solicitamos que, en la medida de lo posible, evite incluir datos personales de terceros en las fotografías.</p>
    <p>TEQMED <strong>no recopila datos sensibles</strong> (datos de salud de pacientes, origen étnico, creencias religiosas, datos biométricos u otros contemplados en el artículo 16 bis de la Ley 19.628 modificada).</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art4">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 4</span>
      <h2 class="pp-art-title">Finalidad del tratamiento</h2>
    </div>
    <p>Los datos personales son tratados exclusivamente para las siguientes finalidades:</p>
    <ol>
      <li><strong>Gestión de llamados de soporte técnico:</strong> recibir, registrar, asignar, dar seguimiento y resolver las solicitudes de soporte técnico reportadas a través del Portal.</li>
      <li><strong>Comunicación con el usuario:</strong> enviar notificaciones por correo electrónico (y, cuando esté habilitado, por WhatsApp) sobre el estado de los llamados, la asignación de técnicos y la resolución del servicio.</li>
      <li><strong>Identificación y autenticación:</strong> verificar la identidad del contacto autorizado y asociarlo al centro médico correspondiente.</li>
      <li><strong>Generación de informes técnicos:</strong> los datos del contacto que reporta una falla pueden vincularse a informes correctivos generados como resultado del llamado, los cuales forman parte de la documentación técnica del servicio.</li>
      <li><strong>Seguridad del sistema:</strong> proteger el Portal contra accesos no autorizados, ataques automatizados y usos abusivos mediante el registro temporal de direcciones IP.</li>
    </ol>
    <p>TEQMED <strong>no utiliza los datos personales</strong> recopilados a través del Portal para fines de marketing, elaboración de perfiles, toma de decisiones automatizadas, ni los comparte con terceros para fines comerciales.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art5">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 5</span>
      <h2 class="pp-art-title">Base legal del tratamiento</h2>
    </div>
    <p>El tratamiento de datos personales se sustenta en las siguientes bases legales, conforme al artículo 13 de la Ley 19.628 modificada por la Ley 21.719:</p>
    <ul>
      <li><strong>Ejecución de un contrato</strong> (Art. 13 letra c): el tratamiento de datos de los contactos de clientes es necesario para la ejecución del contrato de prestación de servicios de mantenimiento celebrado entre TEQMED y la institución a la que pertenece el usuario. La gestión de llamados de soporte es una obligación contractual derivada de dicho contrato.</li>
      <li><strong>Consentimiento del titular</strong> (Art. 13 letra a): cuando un usuario se registra como nuevo contacto a través del Portal, su consentimiento explícito es requerido antes de que sus datos sean almacenados. Este consentimiento es libre, informado, específico e inequívoco.</li>
      <li><strong>Interés legítimo</strong> (Art. 13 letra e): el registro temporal de direcciones IP para la protección del Portal contra ataques y abusos se sustenta en el interés legítimo de TEQMED en mantener la seguridad de sus sistemas, sin que ello prevalezca sobre los derechos fundamentales del titular.</li>
    </ul>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art6">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 6</span>
      <h2 class="pp-art-title">Destinatarios de los datos</h2>
    </div>
    <p>Los datos personales pueden ser compartidos con las siguientes categorías de destinatarios, exclusivamente en la medida necesaria para cumplir las finalidades descritas:</p>
    <ul>
      <li><strong>Personal interno de TEQMED:</strong> técnicos y administradores que acceden a los datos a través del sistema de gestión interno para la asignación y resolución de llamados.</li>
      <li><strong>Proveedor de alojamiento web:</strong> nuestro proveedor de hosting procesa los datos al alojar el Portal y gestionar el envío de correos electrónicos. Opera como encargado del tratamiento.</li>
      <li><strong>Proveedor de almacenamiento en la nube (Cloudflare):</strong> los informes técnicos firmados, que pueden contener nombres de contactos, son respaldados en Cloudflare R2. Cloudflare actúa como encargado del tratamiento bajo su Acuerdo de Procesamiento de Datos (DPA).</li>
      <li><strong>Proveedor de mensajería (Meta — WhatsApp Business API):</strong> cuando las notificaciones por WhatsApp están habilitadas, el número de teléfono y nombre del contacto son transmitidos a Meta para el envío de mensajes. Meta opera bajo sus propios términos de servicio y su DPA.</li>
    </ul>
    <p>TEQMED <strong>no vende, alquila ni cede</strong> datos personales a terceros para fines ajenos a los descritos en esta política.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art7">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 7</span>
      <h2 class="pp-art-title">Transferencia internacional de datos</h2>
    </div>
    <p>Algunos de los proveedores mencionados en el artículo anterior almacenan o procesan datos fuera de Chile:</p>
    <ul>
      <li><strong>Cloudflare, Inc.</strong> (Estados Unidos): los respaldos de informes técnicos en formato PDF son almacenados en infraestructura de Cloudflare R2. Cloudflare cumple con cláusulas contractuales tipo y mantiene un DPA conforme a estándares internacionales de protección de datos.</li>
      <li><strong>Meta Platforms, Inc.</strong> (Estados Unidos): cuando las notificaciones por WhatsApp están habilitadas, los datos de contacto son procesados por Meta. Meta dispone de un DPA y mecanismos de transferencia internacional.</li>
    </ul>
    <p>Estas transferencias se realizan conforme al artículo 16 de la Ley 19.628 modificada, asegurándonos de que los destinatarios ofrezcan niveles de protección adecuados a través de cláusulas contractuales u otros mecanismos reconocidos.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art8">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 8</span>
      <h2 class="pp-art-title">Plazos de conservación</h2>
    </div>
    <p>Los datos personales son conservados únicamente durante el tiempo necesario para cumplir con las finalidades para las que fueron recopilados:</p>

    <div class="pp-table-wrap">
      <table class="pp-table">
        <thead>
          <tr>
            <th>Datos</th>
            <th>Plazo de conservación</th>
            <th>Criterio</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Datos de identificación y contacto</td>
            <td>Mientras exista relación contractual vigente con el cliente</td>
            <td>Necesidad contractual</td>
          </tr>
          <tr>
            <td>Llamados y su contenido</td>
            <td>5 años desde la resolución</td>
            <td>Trazabilidad técnica y obligaciones regulatorias</td>
          </tr>
          <tr>
            <td>Informes técnicos vinculados</td>
            <td>Indefinido mientras sean requeridos por normativa sectorial</td>
            <td>Obligación legal del sector equipamiento médico</td>
          </tr>
          <tr>
            <td>Direcciones IP (rate limiting)</td>
            <td>24 horas</td>
            <td>Seguridad del sistema</td>
          </tr>
          <tr>
            <td>Registros de auditoría</td>
            <td>24 meses</td>
            <td>Trazabilidad y seguridad</td>
          </tr>
        </tbody>
      </table>
    </div>

    <p>Una vez cumplidos los plazos indicados, los datos serán eliminados o anonimizados de forma irreversible, salvo que exista una obligación legal que requiera su conservación por un período mayor.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art9">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 9</span>
      <h2 class="pp-art-title">Derechos de los titulares</h2>
    </div>
    <p>De conformidad con la Ley 19.628 modificada por la Ley 21.719, usted tiene derecho a:</p>
    <ul>
      <li><strong>Derecho de acceso</strong> (Art. 5): solicitar confirmación de si sus datos personales están siendo tratados y, en caso afirmativo, obtener una copia de los mismos y la información sobre su tratamiento.</li>
      <li><strong>Derecho de rectificación</strong> (Art. 6): solicitar la corrección de datos personales inexactos o incompletos.</li>
      <li><strong>Derecho de supresión</strong> (Art. 7): solicitar la eliminación de sus datos personales cuando ya no sean necesarios para la finalidad para la que fueron recopilados, cuando retire su consentimiento, o cuando el tratamiento sea ilícito.</li>
      <li><strong>Derecho de oposición</strong> (Art. 8 bis): oponerse al tratamiento de sus datos personales en determinadas circunstancias, incluido el tratamiento basado en interés legítimo.</li>
      <li><strong>Derecho a la portabilidad</strong> (Art. 9): solicitar la entrega de sus datos personales en un formato estructurado, de uso común y lectura mecánica, o su transmisión directa a otro responsable cuando sea técnicamente posible.</li>
    </ul>

    <div class="pp-highlight">
      <strong>Limitaciones:</strong> el ejercicio del derecho de supresión puede estar limitado cuando los datos sean necesarios para el cumplimiento de obligaciones legales, la formulación o defensa de reclamaciones, o la integridad de informes técnicos ya firmados y respaldados conforme a la normativa sectorial.
    </div>

    <p>TEQMED responderá a las solicitudes de ejercicio de derechos dentro de un plazo máximo de <strong>15 días hábiles</strong> contados desde la recepción de la solicitud, conforme a lo establecido en la ley. Este plazo podrá extenderse por un período igual en caso de solicitudes complejas o múltiples, previa comunicación al titular.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art10">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 10</span>
      <h2 class="pp-art-title">Ejercicio de derechos</h2>
    </div>
    <p>Para ejercer cualquiera de los derechos descritos en el artículo anterior, puede contactarnos a través de:</p>
    <div class="pp-highlight">
      <strong>Correo electrónico:</strong> contacto@teqmed.cl<br>
      <strong>Asunto sugerido:</strong> "Solicitud de derechos de datos personales"<br>
      <strong>Dirección postal:</strong> TEQMED SpA, Castellón 970, Concepción, Región del Biobío, Chile
    </div>
    <p>Para procesar su solicitud, le pediremos que se identifique adecuadamente y especifique el derecho que desea ejercer. TEQMED podrá solicitar información adicional cuando sea necesario para verificar su identidad y evitar el acceso no autorizado a datos de terceros.</p>
    <p>Si considera que el tratamiento de sus datos personales infringe la normativa vigente, tiene derecho a presentar una reclamación ante la <strong>Agencia de Protección de Datos Personales</strong> de Chile.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art11">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 11</span>
      <h2 class="pp-art-title">Cookies y tecnologías similares</h2>
    </div>
    <p>El Portal utiliza las siguientes tecnologías:</p>
    <ul>
      <li><strong>Cookie de sesión:</strong> una cookie técnica estrictamente necesaria para el funcionamiento del Portal. Almacena un identificador de sesión anónimo que permite mantener su sesión activa mientras navega. Se configura con los atributos <em>Secure</em>, <em>HttpOnly</em> y <em>SameSite=Lax</em> para su protección. Se elimina automáticamente al cerrar el navegador o al expirar la sesión.</li>
      <li><strong>Token CSRF:</strong> un token de seguridad almacenado en la sesión del servidor que protege contra ataques de falsificación de solicitudes. No contiene datos personales.</li>
    </ul>
    <p>El Portal <strong>no utiliza cookies de terceros</strong>, cookies de seguimiento, cookies publicitarias ni herramientas de analítica web. No se realizan perfiles de navegación ni seguimiento del comportamiento del usuario.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art12">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 12</span>
      <h2 class="pp-art-title">Seguridad de los datos</h2>
    </div>
    <p>TEQMED implementa medidas técnicas y organizativas apropiadas para proteger los datos personales contra el acceso no autorizado, la alteración, la divulgación o la destrucción, conforme al artículo 14 quinquies de la ley. Entre ellas:</p>
    <ul>
      <li>Cifrado de todas las comunicaciones mediante protocolo HTTPS/TLS, con cabecera HSTS habilitada.</li>
      <li>Almacenamiento de contraseñas con algoritmo de hash seguro (bcrypt).</li>
      <li>Protección contra ataques CSRF, inyección de código y falsificación de solicitudes.</li>
      <li>Limitación de tasa de acceso (rate limiting) para prevenir ataques automatizados.</li>
      <li>Validación de tipo MIME real en archivos subidos.</li>
      <li>Cabeceras de seguridad HTTP: Content-Security-Policy, X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy.</li>
      <li>Control de acceso basado en roles con principio de privilegio mínimo.</li>
      <li>Registro de auditoría de operaciones sensibles.</li>
    </ul>
    <p>Ningún sistema de transmisión o almacenamiento de datos puede garantizar una seguridad absoluta. En caso de producirse una vulneración de seguridad que pueda afectar sus datos personales, TEQMED le notificará conforme a lo dispuesto en la legislación vigente.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art13">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 13</span>
      <h2 class="pp-art-title">Modificaciones a esta política</h2>
    </div>
    <p>TEQMED se reserva el derecho de modificar esta política de privacidad en cualquier momento para adaptarla a cambios normativos, jurisprudenciales o de nuestras prácticas de tratamiento de datos.</p>
    <p>Cualquier modificación será publicada en esta misma página con la fecha de actualización correspondiente. En caso de cambios sustanciales que afecten la forma en que tratamos sus datos personales, le notificaremos a través del correo electrónico registrado en nuestro sistema, con al menos <strong>30 días de anticipación</strong> a la entrada en vigencia de los cambios.</p>
    <p>El uso continuado del Portal después de la entrada en vigencia de las modificaciones constituye la aceptación de la política actualizada.</p>
  </div>

  <!-- ══════════════════════════════════════════════════ -->
  <div class="pp-article" id="art14">
    <div class="pp-art-header">
      <span class="pp-art-num">Art. 14</span>
      <h2 class="pp-art-title">Legislación aplicable</h2>
    </div>
    <p>Esta política de privacidad se rige por la legislación chilena, en particular:</p>
    <ul>
      <li>Ley 19.628 sobre Protección de la Vida Privada, modificada por la <strong>Ley 21.719</strong> que establece normas sobre protección de los datos personales.</li>
      <li>Las normas complementarias y reglamentos que emita la Agencia de Protección de Datos Personales en ejercicio de sus atribuciones.</li>
    </ul>
    <p>Para cualquier controversia derivada del tratamiento de datos personales, serán competentes los tribunales ordinarios de justicia de la ciudad de Concepción, Región del Biobío, Chile, sin perjuicio de las competencias de la Agencia de Protección de Datos Personales.</p>
  </div>

  <footer class="pp-footer">
    <p><strong>TEQMED SpA</strong> — Castellón 970, Concepción, Región del Biobío, Chile</p>
    <p>Contacto: contacto@teqmed.cl</p>
    <p style="margin-top:8px">Este documento fue redactado en cumplimiento de la Ley 21.719 y la Ley 19.628 modificada. Se recomienda su revisión periódica por asesoría legal especializada.</p>
  </footer>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
