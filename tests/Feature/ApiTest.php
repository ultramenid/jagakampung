<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiTest extends TestCase
{
    private function createUserAndKonflik(array $overrides = []): void
    {
        DB::table('users')->insert([
            'name' => 'API User',
            'email' => 'api@test.com',
            'password' => Hash::make('password'),
            'instansi' => 'Test',
            'role' => 0,
            'is_active' => 1,
        ]);

        $defaults = [
            'provinsi' => 'Jawa Timur',
            'kabkota' => 'Surabaya',
            'kecamatan' => 'Tegalsari',
            'desa' => 'Kedungdoro',
            'lat' => '-7.260',
            'long' => '112.740',
            'luas' => 100,
            'kk' => 50,
            'group' => 'Group A',
            'perusahaan' => 'PT ABC',
            'status' => 'aktif',
            'deskripsikonflik' => 'Test',
            'deskripsiperjuangan' => 'Test',
            'user_id' => 1,
        ];

        DB::table('konflik')->insert(array_merge($defaults, $overrides));
    }

    public function test_rest_map_returns_geojson(): void
    {
        $this->createUserAndKonflik();

        $response = $this->get('/cms/rest-map');

        $response->assertStatus(200);

        $data = json_decode($response->getContent(), true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('type', $data);
        $this->assertEquals('FeatureCollection', $data['type']);
        $this->assertArrayHasKey('features', $data);
        $this->assertCount(1, $data['features']);

        $feature = $data['features'][0];
        $this->assertEquals('Feature', $feature['type']);
        $this->assertArrayHasKey('geometry', $feature);
        $this->assertArrayHasKey('properties', $feature);
        $this->assertEquals('Point', $feature['geometry']['type']);
    }

    public function test_rest_map_unauthenticated_access(): void
    {
        $response = $this->get('/cms/rest-map');
        $response->assertStatus(200);
    }

    public function test_rest_map_returns_empty_when_no_data(): void
    {
        $response = $this->get('/cms/rest-map');

        $content = $response->getContent();
        $data = json_decode($content, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('features', $data);
        $this->assertCount(0, $data['features']);
    }

    public function test_rest_map_includes_status_and_coordinates(): void
    {
        $this->createUserAndKonflik([
            'lat' => '-8.500',
            'long' => '115.200',
            'status' => 'potensi',
        ]);

        $response = $this->get('/cms/rest-map');
        $data = json_decode($response->getContent(), true);

        $feature = $data['features'][0];
        $this->assertEquals('potensi', $feature['properties']['status']);
        $this->assertEquals(['-8.500', '115.200'], $feature['geometry']['coordinates']);
    }

    public function test_rest_map_returns_multiple_konflik(): void
    {
        DB::table('users')->insert([
            'name' => 'API User',
            'email' => 'api2@test.com',
            'password' => Hash::make('password'),
            'instansi' => 'Test',
            'role' => 0,
            'is_active' => 1,
        ]);

        DB::table('konflik')->insert([
            ['provinsi' => 'A', 'kabkota' => 'B', 'kecamatan' => 'C', 'desa' => 'D1', 'lat' => '-7.1', 'long' => '112.1', 'luas' => 10, 'kk' => 5, 'group' => 'G', 'perusahaan' => 'P1', 'status' => 'aktif', 'deskripsikonflik' => 'T1', 'deskripsiperjuangan' => 'T1', 'user_id' => 1],
            ['provinsi' => 'A', 'kabkota' => 'B', 'kecamatan' => 'C', 'desa' => 'D2', 'lat' => '-7.2', 'long' => '112.2', 'luas' => 20, 'kk' => 10, 'group' => 'G', 'perusahaan' => 'P2', 'status' => 'draft', 'deskripsikonflik' => 'T2', 'deskripsiperjuangan' => 'T2', 'user_id' => 1],
            ['provinsi' => 'A', 'kabkota' => 'B', 'kecamatan' => 'C', 'desa' => 'D3', 'lat' => '-7.3', 'long' => '112.3', 'luas' => 30, 'kk' => 15, 'group' => 'G', 'perusahaan' => 'P3', 'status' => 'potensi', 'deskripsikonflik' => 'T3', 'deskripsiperjuangan' => 'T3', 'user_id' => 1],
        ]);

        $response = $this->get('/cms/rest-map');
        $data = json_decode($response->getContent(), true);

        $this->assertCount(3, $data['features']);
    }

    public function test_rest_map_includes_all_required_properties(): void
    {
        $this->createUserAndKonflik();

        $response = $this->get('/cms/rest-map');
        $data = json_decode($response->getContent(), true);

        $properties = $data['features'][0]['properties'];
        $this->assertArrayHasKey('id', $properties);
        $this->assertArrayHasKey('status', $properties);
        $this->assertArrayHasKey('lat', $properties);
        $this->assertArrayHasKey('long', $properties);
        $this->assertArrayHasKey('user_id', $properties);
    }
}
