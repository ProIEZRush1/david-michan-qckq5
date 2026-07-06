import { chromium } from 'playwright';

const BASE_URL = process.env.SMOKE_BASE_URL || 'http://127.0.0.1:8123';
const ADMIN_EMAIL = 'david-michan@overcloud.us';
const ADMIN_PASSWORD = '3WfHBI4dMjKR';
const STAMP = Date.now().toString().slice(-6);

function fail(step, error) {
    console.error(`\n✗ FAILED at step: ${step}`);
    console.error(error);
    process.exit(1);
}

async function main() {
    const browser = await chromium.launch();
    const page = await browser.newPage();

    try {
        // ---- Login ----------------------------------------------------------
        await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle' });
        await page.fill('#email', ADMIN_EMAIL);
        await page.fill('#password', ADMIN_PASSWORD);
        await page.click('button:has-text("Iniciar sesión")');
        await page.waitForURL(`${BASE_URL}/dashboard`, { timeout: 15000 });

        const dashboardText = await page.textContent('body');
        if (!dashboardText.includes('David Michan')) {
            throw new Error('El dashboard no muestra el nombre del negocio "David Michan".');
        }
        if (dashboardText.includes('Laravel') || dashboardText.includes("You're logged in")) {
            throw new Error('El dashboard todavía muestra branding genérico de Laravel.');
        }
        console.log('✓ Login y dashboard branded OK');

        // ---- Planes -----------------------------------------------------------
        const planNombre = `Plan E2E ${STAMP}`;
        await page.goto(`${BASE_URL}/planes/create`, { waitUntil: 'networkidle' });
        await page.fill('#nombre', planNombre);
        await page.fill('#precio', '199');
        await page.click('button:has-text("Guardar plan")');
        await page.waitForURL(`${BASE_URL}/planes`, { timeout: 15000 });
        let body = await page.textContent('body');
        if (!body.includes(planNombre)) throw new Error(`El plan "${planNombre}" no aparece en la tabla tras crearlo.`);
        await page.reload({ waitUntil: 'networkidle' });
        body = await page.textContent('body');
        if (!body.includes(planNombre)) throw new Error(`El plan "${planNombre}" no persistió tras recargar.`);
        console.log('✓ Planes: crear + persistencia OK');

        // ---- Inventario ---------------------------------------------------------
        const numero = `55${STAMP}00`;
        await page.goto(`${BASE_URL}/inventario/create`, { waitUntil: 'networkidle' });
        await page.fill('#lada', '55');
        await page.fill('textarea', numero);
        await page.click('button:has-text("Guardar números")');
        await page.waitForURL(`${BASE_URL}/inventario`, { timeout: 15000 });
        body = await page.textContent('body');
        if (!body.includes(numero)) throw new Error(`El número "${numero}" no aparece en el inventario tras crearlo.`);
        await page.reload({ waitUntil: 'networkidle' });
        body = await page.textContent('body');
        if (!body.includes(numero)) throw new Error(`El número "${numero}" no persistió tras recargar.`);
        console.log('✓ Inventario: agregar número + persistencia OK');

        // ---- Clientes -----------------------------------------------------------
        const clienteNombre = `Cliente E2E ${STAMP}`;
        const clienteTelefono = `521550${STAMP}1`;
        await page.goto(`${BASE_URL}/clientes/create`, { waitUntil: 'networkidle' });
        await page.fill('#nombre', clienteNombre);
        await page.fill('#telefono', clienteTelefono);
        await page.click('button:has-text("Guardar cliente")');
        await page.waitForURL(`${BASE_URL}/clientes`, { timeout: 15000 });
        body = await page.textContent('body');
        if (!body.includes(clienteNombre)) throw new Error(`El cliente "${clienteNombre}" no aparece en la tabla tras crearlo.`);
        await page.reload({ waitUntil: 'networkidle' });
        body = await page.textContent('body');
        if (!body.includes(clienteNombre)) throw new Error(`El cliente "${clienteNombre}" no persistió tras recargar.`);
        console.log('✓ Clientes: crear + persistencia OK');

        // ---- Pedidos --------------------------------------------------------------
        const pedidoTelefono = `521550${STAMP}2`;
        await page.goto(`${BASE_URL}/pedidos/create`, { waitUntil: 'networkidle' });
        await page.fill('#nombre', `Pedido E2E ${STAMP}`);
        await page.fill('#telefono', pedidoTelefono);
        await page.selectOption('#plan_id', { index: 1 });
        await page.click('button:has-text("Guardar pedido")');
        await page.waitForURL(`${BASE_URL}/pedidos`, { timeout: 15000 });
        body = await page.textContent('body');
        if (!body.includes(`Pedido E2E ${STAMP}`)) throw new Error('El pedido registrado manualmente no aparece en la tabla.');
        await page.reload({ waitUntil: 'networkidle' });
        body = await page.textContent('body');
        if (!body.includes(`Pedido E2E ${STAMP}`)) throw new Error('El pedido no persistió tras recargar.');
        console.log('✓ Pedidos: crear + persistencia OK');

        // Open the order and walk it through the fulfillment flow.
        await page.click(`tr:has-text("Pedido E2E ${STAMP}") >> text=Ver`);
        await page.waitForURL(/\/pedidos\/\d+$/, { timeout: 15000 });
        await page.click('button:has-text("Marcar como pagado")');
        await page.waitForSelector('.swal2-confirm', { state: 'visible' });
        await Promise.all([
            page.waitForResponse((res) => res.url().includes('/marcar-pagado')),
            page.locator('.swal2-confirm').click(),
        ]);
        await page.waitForFunction(
            () => /Pagado|Número asignado/.test(document.body.innerText),
            null,
            { timeout: 10000 },
        );
        body = await page.textContent('body');
        if (!/Pagado|Número asignado/.test(body)) {
            throw new Error('Tras "Marcar como pagado" el pedido no cambió a un estado de pago/asignación.');
        }
        console.log('✓ Pedidos: flujo de pago/asignación de número OK');

        // ---- Preguntas frecuentes ---------------------------------------------------
        const pregunta = `¿Pregunta E2E ${STAMP}?`;
        await page.goto(`${BASE_URL}/preguntas-frecuentes/create`, { waitUntil: 'networkidle' });
        await page.fill('#pregunta', pregunta);
        await page.fill('#respuesta', 'Respuesta de prueba end-to-end.');
        await page.fill('#palabras_clave', 'e2e,prueba');
        await page.click('button:has-text("Guardar")');
        await page.waitForURL(`${BASE_URL}/preguntas-frecuentes`, { timeout: 15000 });
        body = await page.textContent('body');
        if (!body.includes(pregunta)) throw new Error(`La FAQ "${pregunta}" no aparece en la tabla tras crearla.`);
        await page.reload({ waitUntil: 'networkidle' });
        body = await page.textContent('body');
        if (!body.includes(pregunta)) throw new Error(`La FAQ "${pregunta}" no persistió tras recargar.`);
        console.log('✓ Preguntas frecuentes: crear + persistencia OK');

        // ---- Conectar WhatsApp page renders without error --------------------------
        await page.goto(`${BASE_URL}/conectar`, { waitUntil: 'networkidle' });
        body = await page.textContent('body');
        if (body.includes('500') && body.includes('Server Error')) {
            throw new Error('La página /conectar arrojó un error de servidor.');
        }
        console.log('✓ Conectar WhatsApp: renderiza sin errores');

        console.log('\nTODO EN VERDE ✔');
    } catch (error) {
        fail('flujo principal', error);
    } finally {
        await browser.close();
    }
}

main();
