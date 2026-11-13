<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_nao_autenticado_nao_pode_listar_usuarios(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertUnauthorized();
    }

    public function test_usuario_autenticado_pode_listar_somente_a_propria_conta(): void
    {
        $usuario = User::factory()->create();
        $outroUsuario = User::factory()->create();

        Sanctum::actingAs($usuario);

        $response = $this->getJson('/api/users');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $usuario->id);

        $this->assertDatabaseHas('users', [
            'id' => $outroUsuario->id,
        ]);
    }

    public function test_usuario_pode_consultar_a_propria_conta(): void
    {
        $usuario = User::factory()->create();

        Sanctum::actingAs($usuario);

        $response = $this->getJson(
            "/api/users/{$usuario->id}"
        );

        $response->assertOk()
            ->assertJsonPath('data.id', $usuario->id);
    }

    public function test_usuario_nao_pode_consultar_outra_conta(): void
    {
        $usuario = User::factory()->create();
        $outroUsuario = User::factory()->create();

        Sanctum::actingAs($usuario);

        $response = $this->getJson(
            "/api/users/{$outroUsuario->id}"
        );

        $response->assertNotFound();
    }

    public function test_usuario_nao_pode_atualizar_outra_conta(): void
    {
        $usuario = User::factory()->create();
        $outroUsuario = User::factory()->create();

        Sanctum::actingAs($usuario);

        $response = $this->putJson(
            "/api/users/{$outroUsuario->id}",
            [
                'name' => 'Nome Alterado',
            ]
        );

        $response->assertNotFound();

        $this->assertDatabaseHas('users', [
            'id' => $outroUsuario->id,
            'name' => $outroUsuario->name,
        ]);
    }

    public function test_usuario_nao_pode_excluir_outra_conta(): void
    {
        $usuario = User::factory()->create();
        $outroUsuario = User::factory()->create();

        Sanctum::actingAs($usuario);

        $response = $this->deleteJson(
            "/api/users/{$outroUsuario->id}"
        );

        $response->assertNotFound();

        $this->assertDatabaseHas('users', [
            'id' => $outroUsuario->id,
        ]);
    }

    public function test_usuario_pode_atualizar_a_propria_conta(): void
    {
        $usuario = User::factory()->create();

        Sanctum::actingAs($usuario);

        $response = $this->putJson(
            "/api/users/{$usuario->id}",
            [
                'name' => 'Nome Atualizado',
            ]
        );

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $usuario->id,
            'name' => 'Nome Atualizado',
        ]);
    }

    public function test_usuario_pode_excluir_a_propria_conta(): void
    {
        $usuario = User::factory()->create();

        Sanctum::actingAs($usuario);

        $response = $this->deleteJson(
            "/api/users/{$usuario->id}"
        );

        $response->assertNoContent();

        $this->assertDatabaseMissing('users', [
            'id' => $usuario->id,
        ]);
    }
}
