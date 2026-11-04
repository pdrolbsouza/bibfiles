<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class RelatorioPedidosTest extends DuskTestCase
{
    public function test_relatorio_pedidos(): void
    {
        $authorizedPath = storage_path('framework/testing/dusk-relatorio-autorizado.pdf');
        $deniedPath = storage_path('framework/testing/dusk-relatorio-negado.pdf');
        $pdfContent = "%PDF-1.4\n";

        file_put_contents($authorizedPath, $pdfContent);
        file_put_contents($deniedPath, $pdfContent);

        try {
            $this->browse(function (Browser $browser) use ($authorizedPath, $deniedPath) {
                # Login
                $browser->visit('/')
                    ->clickLink('Entrar')
                    ->waitFor('#loginUsuario')
                    ->type('#loginUsuario', '1111')
                    ->press('Login')
                    ->waitForLocation('/')

                # Criação de Arquivos
                    ->visit('/files/create')
                    ->attach('file', $authorizedPath)
                    ->type('input[name="name"]', 'Arquivo Dusk relatório autorizado')
                    ->press('Enviar')
                    ->waitForLocation('/files')
                    ->assertSee('Arquivo Dusk relatório autorizado')

                    ->visit('/files/create')
                    ->attach('file', $deniedPath)
                    ->type('input[name="name"]', 'Arquivo Dusk relatório negado')
                    ->press('Enviar')
                    ->waitForLocation('/files')
                    ->assertSee('Arquivo Dusk relatório negado')

                # Login como usuário comum
                    ->click('.login_logout_link')
                    ->clickLink('Entrar')
                    ->waitFor('#loginUsuario')
                    ->type('#loginUsuario', '2222')
                    ->press('Login')
                    ->waitForLocation('/')

                # Solicitando acesso aos arquivos
                    ->visit('/dusk-relatorio-autorizado.pdf')
                    ->waitFor('form[action="/novopedido"]')
                    ->type('input[name="nome"]', 'UsuarioDuskRelatorio')
                    ->type('input[name="email"]', 'dusk-relatorio-autorizado@example.test')
                    ->type('textarea[name="finalidade"]', 'Teste do relatório autorizado')
                    ->type('input[name="captcha"]', env('DUSK_CAPTCHA', 'dusk-captcha'))
                    ->press('Enviar Pedido')
                    ->waitForLocation('/')
                    ->assertSee('Solicitação de acesso enviada com sucesso')

                    ->visit('/dusk-relatorio-negado.pdf')
                    ->waitFor('form[action="/novopedido"]')
                    ->type('input[name="nome"]', 'UsuarioDuskRelatorio')
                    ->type('input[name="email"]', 'dusk-relatorio-negado@example.test')
                    ->type('textarea[name="finalidade"]', 'Teste do relatório negado')
                    ->type('input[name="captcha"]', env('DUSK_CAPTCHA', 'dusk-captcha'))
                    ->press('Enviar Pedido')
                    ->waitForLocation('/')
                    ->assertSee('Solicitação de acesso enviada com sucesso')

                # Trocando usuários
                    ->click('.login_logout_link')
                    ->clickLink('Entrar')
                    ->waitFor('#loginUsuario')
                    ->type('#loginUsuario', '1111')
                    ->press('Login')
                    ->waitForLocation('/')

                # Visualizando e autorizando/negando pedidos
                    ->visit('/pendentes')
                    ->waitFor('tbody tr')
                    ->click('tbody tr:first-child summary')
                    ->press('Autorizar Pedido')
                    ->acceptDialog()
                    ->waitForText('Autorização do arquivo enviada com sucesso')

                    ->visit('/pendentes')
                    ->waitFor('tbody tr')
                    ->click('tbody tr:first-child summary')
                    ->type('tbody tr:first-child textarea[name="justificativa"]', 'Acesso negado no teste do relatório')
                    ->press('Negar Pedido')
                    ->acceptDialog()
                    ->waitForText('Aviso sobre o arquivo enviado com sucesso')

                # Relatório dos pedidos
                    ->visit('/pedidos_realizados')
                    ->waitFor('table tbody tr')
                    ->assertSee('Arquivo Dusk relatório autorizado')
                    ->assertSee('dusk-relatorio-autorizado.pdf')
                    ->assertSee('Arquivo Dusk relatório negado')
                    ->assertSee('dusk-relatorio-negado.pdf')
                    ->assertAttribute('a.btn-info', 'href', '/gerarExcel/?busca=')

                    ->type('input[name="busca"]', 'UsuarioDuskRelatorio')
                    ->press('Buscar')
                    ->waitForLocation('/pedidos_realizados')
                    ->assertInputValue('input[name="busca"]', 'UsuarioDuskRelatorio')
                    ->assertSee('Arquivo Dusk relatório autorizado')
                    ->assertSee('Arquivo Dusk relatório negado')
                    ->assertAttribute(
                        'a.btn-info',
                        'href',
                        '/gerarExcel/?busca=UsuarioDuskRelatorio'
                    )

                    # Excluindo arquivos
                    ->visit('/files')
                    ->waitFor('tr[data-file-name="Arquivo Dusk relatório autorizado"] button.botao')
                    ->click('tr[data-file-name="Arquivo Dusk relatório autorizado"] button.botao')
                    ->acceptDialog()
                    ->waitForLocation('/files')
                    ->assertDontSee('Arquivo Dusk relatório autorizado')
                    ->waitFor('tr[data-file-name="Arquivo Dusk relatório negado"] button.botao')
                    ->click('tr[data-file-name="Arquivo Dusk relatório negado"] button.botao')
                    ->acceptDialog()
                    ->waitForLocation('/files')
                    ->assertDontSee('Arquivo Dusk relatório negado');
            });
        } finally {
            @unlink($authorizedPath);
            @unlink($deniedPath);
        }
    }
}
