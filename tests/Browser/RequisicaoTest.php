<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class RequisicaoTest extends DuskTestCase
{
    public function test_Pedidos(): void
    {
        $authorizedPath = storage_path('framework/testing/dusk-autorizado.pdf');
        $deniedPath = storage_path('framework/testing/dusk-negado.pdf');
        $pdfContent = "%PDF-1.4\n";

        file_put_contents($authorizedPath, $pdfContent);
        file_put_contents($deniedPath, $pdfContent);

        try {
            $this->browse(function (Browser $browser) use ($authorizedPath, $deniedPath) {
            $browser->visit('/')

                # Fazendo o login como usuário autorizado
                ->clickLink('Entrar')
                ->waitFor('#loginUsuario')
                ->type('#loginUsuario', '1111')
                ->press('Login')
                ->pause(1000)

                # Enviando arquivos
                # Arquivo Autorizado
                ->waitForLocation('/')
                ->visit('/files/create')
                ->attach('file', $authorizedPath)
                ->type('input[name="name"]', 'Arquivo Dusk autorizado')
                ->press('Enviar')
                ->pause(1000)
                ->assertPathIs('/files')
                ->assertSee('Arquivo Dusk autorizado')

                # Arquivo Negado
                ->visit('/files/create')
                ->attach('file', $deniedPath)
                ->type('input[name="name"]', 'Arquivo Dusk negado')
                ->press('Enviar')
                ->pause(1000)
                ->assertPathIs('/files')
                ->assertSee('Arquivo Dusk negado')

                # Logout e login como usuário comum
                ->click('.login_logout_link')
                ->pause(1000)
                ->clickLink('Entrar')
                ->waitFor('#loginUsuario')
                ->type('#loginUsuario', '2222')
                ->press('Login')
                
                # Solicitando acesso aos arquivos
                # Arquivo 1
                ->waitForLocation('/')
                ->visit('/dusk-autorizado.pdf')
                ->waitFor('form[action="/novopedido"]')
                ->type('input[name="nome"]', 'Usuário Dusk')
                ->type('input[name="email"]', 'dusk-autorizado@example.test')
                ->type('textarea[name="finalidade"]', 'Teste de autorização')
                ->type('input[name="captcha"]', env('DUSK_CAPTCHA', 'dusk-captcha'))
                ->press('Enviar Pedido')
                ->pause(1000)
                ->waitForLocation('/')

                # Arquivo 2
                ->assertSee('Solicitação de acesso enviada com sucesso')
                ->visit('/dusk-negado.pdf')
                ->waitFor('form[action="/novopedido"]')
                ->type('input[name="nome"]', 'Usuário Dusk')
                ->type('input[name="email"]', 'dusk-negado@example.test')
                ->type('textarea[name="finalidade"]', 'Teste de negação')
                ->type('input[name="captcha"]', env('DUSK_CAPTCHA', 'dusk-captcha'))
                ->press('Enviar Pedido')
                ->pause(1000)
                ->waitForLocation('/')
                ->assertSee('Solicitação de acesso enviada com sucesso')

                # Logout e login como usuário autorizado
                ->click('.login_logout_link')
                ->pause(1000)
                ->clickLink('Entrar')
                ->waitFor('#loginUsuario')
                ->type('#loginUsuario', '1111')
                ->press('Login')
                ->waitForLocation('/')

                # Acessando a página de pendentes
                ->visit('/pendentes')
                ->waitFor('tbody tr')
                ->pause(500)
                ->assertSee('Arquivo Dusk autorizado')
                ->assertSee('Arquivo Dusk negado')
                ->click('tbody tr:first-child summary')
                ->pause(500)
                ->press('Autorizar Pedido')
                ->acceptDialog()
                ->pause(1000)
                ->assertSee('Autorização do arquivo enviada com sucesso')

                # Acessando a página de pendentes novamente para negar o segundo pedido
                ->visit('/pendentes')
                ->waitFor('tbody tr')
                ->pause(500)
                ->click('tbody tr:first-child summary')
                ->pause(500)
                ->type('tbody tr:first-child textarea[name="justificativa"]', 'Acesso negado no teste')
                ->press('Negar Pedido')
                ->acceptDialog()
                ->pause(1000)
                ->assertSee('Aviso sobre o arquivo enviado com sucesso')

                # Excluindo os arquivos enviados pelo teste
                ->visit('/files')
                ->waitFor('tr[data-file-name="Arquivo Dusk autorizado"] button.botao')
                ->assertSee('Arquivo Dusk autorizado')
                ->assertSee('Arquivo Dusk negado')
                ->click('tr[data-file-name="Arquivo Dusk autorizado"] button.botao')
                ->acceptDialog()
                ->pause(1000)
                ->waitForLocation('/files')
                ->assertDontSee('Arquivo Dusk autorizado')
                ->waitFor('tr[data-file-name="Arquivo Dusk negado"] button.botao')
                ->click('tr[data-file-name="Arquivo Dusk negado"] button.botao')
                ->acceptDialog()
                ->pause(1000)
                ->waitForLocation('/files')
                ->assertDontSee('Arquivo Dusk negado');
            });
        } finally {
            @unlink($authorizedPath);
            @unlink($deniedPath);
        }
    }
}
