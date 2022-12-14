<?php
declare(strict_types=1);

use Cake\Auth\DefaultPasswordHasher;
use Migrations\AbstractSeed;

/**
 * TblUsers seed.
 */
class UsersSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     *
     * @return void
     */
    public function run()
    {
        $hash = new DefaultPasswordHasher();

        $data = [
            [
                'name' => 'Usuario',
                'lastname' => 'Admin',
                'email' => 'aloisejulian+admin@gmail.com',
                'password' => $hash->hash('admin@123'),
                'group_id' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
                'deleted' => null,
                'signature' => null,
            ],
            [
                'name' => 'Usuario',
                'lastname' => 'Auditor',
                'email' => 'aloisejulian+auditor@gmail.com',
                'password' => $hash->hash('auditor@123'),
                'group_id' => 2,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
                'deleted' => null,
                'license' => '1234',
                'licenseNational' => '5678',
                'phone' => 1155556666,
                'document' => 12345345,
                'area' => 'medica',
	            'signature' => 'image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj48c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgdmVyc2lvbj0iMS4xIiB3aWR0aD0iNDYwIiBoZWlnaHQ9IjE1NSI+PHBhdGggZmlsbD0ibm9uZSIgc3Ryb2tlPSIjMDAwMDAwIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIgZD0iTSAxIDc3IGMgMC4wOSAtMC4wNyAzLjIyIC0yLjkgNSAtNCBjIDIuNDkgLTEuNTMgNS44NCAtMi4zNCA4IC00IGMgMS44OCAtMS40NSAzLjIgLTQuNTYgNSAtNiBjIDEuMjQgLTAuOTkgMy41MyAtMS4wNyA1IC0yIGMgNC43IC0yLjk3IDkuNjMgLTYuMjYgMTQgLTEwIGMgNy4zNiAtNi4zMSAxOS4zMyAtMTkuMjYgMjEgLTIwIGMgMC43MSAtMC4zMiAtMS43MSA4LjAxIC0zIDEyIGMgLTIuNzggOC41NiAtNS44NSAxNy42NSAtOSAyNSBjIC0wLjUgMS4xNyAtMi41NCAxLjg2IC0zIDMgYyAtMS4zMyAzLjMzIC0xLjM2IDguNzEgLTMgMTIgYyAtMS40MSAyLjgzIC01LjM3IDUuMzYgLTcgOCBjIC0wLjc4IDEuMjcgLTAuMTQgMy44OCAtMSA1IGMgLTIuMDQgMi42NSAtNS44MiA1LjUzIC05IDggYyAtMi44NiAyLjIyIC02Ljc2IDUuNjggLTkgNiBjIC0xLjMzIDAuMTkgLTQuMzYgLTIuNzIgLTUgLTQgYyAtMC40MSAtMC44MiAwLjMxIC0zLjAyIDEgLTQgYyAxLjQxIC0yLjAxIDMuNjggLTUuNTMgNiAtNiBjIDI3LjE1IC01LjQ4IDY3LjE3IC05Ljg2IDk4IC0xNSBjIDEuMzkgLTAuMjMgMi42NyAtMS45OCA0IC0yIGMgNDguMzMgLTAuNTggMTA2LjM2IC0wLjU3IDE2MiAwIGMgMTEuNyAwLjEyIDIyLjU4IDAuOTkgMzQgMiBjIDMuNzUgMC4zMyAxMS40NyAxLjc1IDExIDIgYyAtMS4yMSAwLjY0IC0xOC40OSA1Ljc4IC0yOCA3IGMgLTI2LjggMy40NCAtNTQuODIgNC4yOSAtODEgNyBjIC0yLjA0IDAuMjEgLTQgMS45MyAtNiAyIGMgLTMyLjI1IDEuMTkgLTY2Ljc1IDEuNzcgLTEwMiAyIGMgLTE2LjU5IDAuMTEgLTMyLjQyIC0wLjE3IC00OCAtMSBjIC0yLjY4IC0wLjE0IC04LjI5IC0xLjU3IC04IC0yIGMgMC40NCAtMC42NiA4IC0yLjM4IDEyIC00IGMgMTMuNzUgLTUuNTUgMjcuNTkgLTEyLjMxIDQwIC0xNyBjIDEuNDMgLTAuNTQgMy4zOSAwLjIzIDUgMCBjIDIuOTcgLTAuNDIgNS45MiAtMS42MiA5IC0yIGMgMTAuNzIgLTEuMzEgMjEuMTMgLTEuNzQgMzIgLTMgYyAzLjc4IC0wLjQ0IDcuMzIgLTEuNzMgMTEgLTIgYyA1LjI2IC0wLjM5IDEwLjcxIDAuMzggMTYgMCBjIDQuMDIgLTAuMjkgNy45NyAtMS45MSAxMiAtMiBjIDI0Ljg2IC0wLjU3IDUzLjY3IC0wLjU1IDc1IDAgYyAxLjAzIDAuMDMgMS45OCAxLjg3IDMgMiBsIDEzIDAiLz48cGF0aCBmaWxsPSJub25lIiBzdHJva2U9IiMwMDAwMDAiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBkPSJNIDEwMyA0MCBjIC0wLjA3IDAuMTIgLTIuNzkgNC41OSAtNCA3IGMgLTAuODEgMS42MSAtMS42OCAzLjM4IC0yIDUgYyAtMC4zIDEuNTEgMC40OSAzLjY2IDAgNSBjIC0wLjcgMS45MyAtMy4wOSAzLjg1IC00IDYgYyAtMy42NCA4LjU5IC02LjkyIDE3LjYzIC0xMCAyNyBjIC00LjEyIDEyLjU3IC02Ljk0IDI0LjM4IC0xMSAzNyBjIC0yLjQ2IDcuNjUgLTUuMTkgMTQuOTcgLTggMjIgYyAtMC40MyAxLjA4IC0yLjA0IDMuMDUgLTIgMyBjIDAuMjUgLTAuMzEgMTAuNzQgLTExLjYgMTQgLTE4IGMgNS4wMyAtOS44NiA4LjM4IC0yMS44OCAxMiAtMzMgYyAxLjA1IC0zLjIzIDAuOTMgLTYuOTggMiAtMTAgYyAwLjg0IC0yLjM5IDMuMzEgLTQuNzUgNCAtNyBjIDAuNTIgLTEuNjggLTAuNDcgLTQuMTcgMCAtNiBjIDIuMDcgLTguMDIgNi40NCAtMTYuNTcgOCAtMjUgYyAyLjM4IC0xMi45MSA0LjQ0IC0yOC4yIDQgLTQwIGMgLTAuMTcgLTQuNDcgLTUuMSAtMTMuNDQgLTYgLTE0IGMgLTAuNTQgLTAuMzQgLTEuNjUgNi4wMiAtMiA5IGMgLTAuMyAyLjU5IDAuMzYgNS41MSAwIDggYyAtMC4yOCAxLjk3IC0xLjcgNC4wNCAtMiA2IGMgLTAuMzMgMi4xNyAwLjQyIDQuODEgMCA3IGMgLTAuODcgNC41NiAtMy4yOSA5LjQxIC00IDE0IGMgLTAuNTggMy43OCAwLjQ0IDguNDUgMCAxMiBjIC0wLjE3IDEuMzIgLTEuOSAyLjY3IC0yIDQgYyAtMC41MSA3LjE0IC0wLjQ5IDE2LjM2IDAgMjQgYyAwLjE1IDIuMzIgMS4wNCA1LjA5IDIgNyBjIDAuNTYgMS4xMSAyLjI3IDMuMjYgMyAzIGMgMS44MiAtMC42NiA2LjM3IC00LjIgOCAtNyBjIDYuMDkgLTEwLjQ3IDExLjI0IC0yMy40OSAxNyAtMzYgYyAyLjIgLTQuNzkgNC4yNCAtOS4xMiA2IC0xNCBjIDQuMzggLTEyLjE3IDExLjE1IC0zMy45OCAxMiAtMzYgYyAwLjExIC0wLjI3IDAuOTYgMy4zMyAxIDUgYyAwLjI5IDEzLjQ1IDAuNTMgMjcuNTYgMCA0MSBjIC0wLjEzIDMuMzQgLTEuNzYgNi42NiAtMiAxMCBjIC0wLjQyIDUuODkgMC40OSAxMi45IDAgMTggYyAtMC4xIDEuMDIgLTEuODcgMS45OCAtMiAzIGMgLTAuNDYgMy42NyAtMC42MSAxMS4xOCAwIDEzIGMgMC4xOSAwLjU4IDMuMDIgLTAuMzEgNCAtMSBjIDIuMDEgLTEuNDEgNC40NCAtMy43NyA2IC02IGMgMi45NiAtNC4yMyA1LjQ4IC05LjExIDggLTE0IGMgMi45NCAtNS43IDUuNyAtMTEuMSA4IC0xNyBjIDMuMSAtNy45NCA1Ljg5IC0xNi4zNCA4IC0yNCBjIDAuNDIgLTEuNTEgLTAuMDQgLTUuMDggMCAtNSBjIDAuMDYgMC4xMSAwLjkyIDQuNjQgMSA3IGMgMC4yNSA3LjM1IC0wLjUgMTUuNzYgMCAyMiBjIDAuMDggMS4wMiAxLjQxIDMuMDggMiAzIGMgMS4wNSAtMC4xNSAzLjY5IC0yLjQ2IDUgLTQgYyAyLjIxIC0yLjYxIDMuOTggLTUuODYgNiAtOSBjIDEuMDkgLTEuNjkgMi4yNSAtMy4zMSAzIC01IGMgMC41MyAtMS4xOSAwLjQ0IC0yLjg3IDEgLTQgYyAwLjY4IC0xLjM3IDIuMzIgLTIuNjMgMyAtNCBjIDAuNTYgLTEuMTMgMC40NCAtMi44NyAxIC00IGMgMC42OCAtMS4zNyAyLjc0IC00LjQ3IDMgLTQgYyAxLjMxIDIuMzggNS44NyAxNS44MiA4IDI0IGMgMS44OSA3LjIzIDIuNDEgMTUuMzggNCAyMiBjIDAuMjYgMS4wNyAxLjc1IDIgMiAzIGMgMC4zNCAxLjM4IC0wLjI3IDMuMzkgMCA1IGMgMS4wNCA2LjI2IDQuNjEgMTYuMTEgNCAxOSBjIC0wLjI1IDEuMTkgLTUuNDMgMC4yMSAtOCAwIGMgLTEuMzMgLTAuMTEgLTIuOCAtMC40MiAtNCAtMSBjIC02LjI2IC0zIC0xMi41OCAtNi45MiAtMTkgLTEwIGMgLTEuODkgLTAuOTEgLTQuNTEgLTEuMDEgLTYgLTIgYyAtMS4xOCAtMC43OCAtMS43NyAtMy4xNSAtMyAtNCBjIC0yLjc0IC0xLjkgLTYuNjEgLTMuNjggLTEwIC01IGMgLTIuNSAtMC45NyAtNS40NiAtMS4wMyAtOCAtMiBjIC00LjQgLTEuNjggLTguNTMgLTQuNTUgLTEzIC02IGMgLTYuNzMgLTIuMTggLTIxLjYzIC00LjcxIC0yMSAtNSBjIDEuMDUgLTAuNDkgMjMuNjMgLTEuMTggMzYgLTIgYyAzLjExIC0wLjIxIDYuMjYgLTAuMzcgOSAtMSBjIDEuMzUgLTAuMzEgMi42MyAtMS43NiA0IC0yIGMgNS44NCAtMS4wMSAxMy4xNiAtMC45OSAxOSAtMiBjIDEuMzcgLTAuMjQgMi42OSAtMS43NiA0IC0yIGMgMi4wNSAtMC4zNyA0LjY2IDAuMjYgNyAwIGMgMTYuMTYgLTEuNzYgMzEuNDEgLTQuMzYgNDggLTYgYyAxOC4yMiAtMS44IDM0LjY4IC0zLjIyIDUzIC00IGMgNDYuMiAtMS45OCA4Ny44NiAtMi4yNiAxMzQgLTQgbCAyNSAtMiIvPjwvc3ZnPg=='
            ],
            [
                'name' => 'Usuario',
                'lastname' => 'Red',
                'email' => 'aloisejulian+red@gmail.com',
                'password' => $hash->hash('red@123'),
                'group_id' => 3,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
                'deleted' => null,
                'signature' => null,
            ],
        ];

        $table = $this->table('users');
        $table->insert($data)->save();
    }
}
