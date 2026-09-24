<?php

namespace Tests\Browser;

use Illuminate\Http\UploadedFile;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class EnviarArquivoTest extends DuskTestCase
{
    public function test_EnviarArquivo(): void
    {
        $file = UploadedFile::fake()->create('dusk-sample.pdf', 10, 'application/pdf');

        $this->browse(function (Browser $browser) use ($file) {
            $browser->visit('/')
                ->clickLink('Entrar')
                ->waitFor('#loginUsuario')
                ->typeSlowly('#loginUsuario', '1111')
                ->press('Login')
                ->visit('/files/create')
                ->attach('file', $file->getPathname())
                ->type('name', 'Arquivo de teste Dusk')
                ->press('Enviar')
                ->assertPathIs('/files')
                ->assertSee('Arquivo de teste Dusk');
        });
    }
}
