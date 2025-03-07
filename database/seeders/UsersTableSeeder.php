<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('users')->delete();
        
        \DB::table('users')->insert(array (
            0 => 
            array (
                'id' => Str::uuid(),
                'name' => 'admin',
                'email' => 'bps7100@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$zz45cZrAmEiqSl0fMiaExOeWbixRedZyXP8HQhNVE/EAXZGj/fE.C',
                'satker_id' => 1,
                'role' => 'admin',
                'remember_token' => 'DosVNJWengLSoAa2kfeJQ1dNPUZGZ1nNHh98FLa8UixPh8klbKnqcC4evLm5',
                'created_at' => '2023-07-17 04:27:48',
                'updated_at' => '2023-07-17 04:27:48',
            ),
            1 => 
            array (
                'id' => Str::uuid(),
                'name' => 'daniel',
                'email' => 'daniel.tri@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$HSKUOrjvwmWpm5tM2XKCqOA8tGYKi6rEU70aej60igGEKkDRh3jWW',
                'satker_id' => 1,
                'role' => 'admin',
                'remember_token' => 'zyGygkl2fTfdl4mas0UUK2O2grXlbbliWEZKl1osEjinHfS4BIO8DfODVayo',
                'created_at' => '2023-08-21 09:17:55',
                'updated_at' => '2024-08-22 07:38:08',
            ),
            2 => 
            array (
                'id' => Str::uuid(),
                'name' => 'dian.teguh',
                'email' => 'dian.teguh@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$ENb3hpT5kN.ADsjAygEZguwREopq3clCH.jQq6Vwgb2FV0eIC.qJe',
                'satker_id' => 1,
                'role' => 'admin',
                'remember_token' => 'ufaFufq3kOjTlijqPYFKNFb5TMjtGJj3p0myevm2RAxcyNta2P7gW6pd1Ozl',
                'created_at' => '2024-06-28 15:18:30',
                'updated_at' => '2024-06-28 15:18:30',
            ),
            3 => 
            array (
                'id' => Str::uuid(),
                'name' => 'loveria',
                'email' => 'loveria.candra@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$Uz6NgcZe3gMNQ5792sWNZ.Tc980.23i7Aq6WK1BJ4Qjv1HGYAJWVC',
                'satker_id' => 1,
                'role' => 'admin',
                'remember_token' => 'n0JN8cStOs99NVhw3HgiEJolLCGNGiqTuTSIIB5D9d9W3QKTXxWgkFlwZ89S',
                'created_at' => '2023-11-22 11:52:12',
                'updated_at' => '2023-11-22 11:52:12',
            ),
            4 => 
            array (
                'id' => Str::uuid(),
                'name' => 'neraca7101',
                'email' => 'bps7101@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$G7ONuQ8weF8KgA/cWwWqmeHqGqMqPI0uTv4S9ONopKy2.KQpmh9FK',
                'satker_id' => 2,
                'role' => 'user',
                'remember_token' => 'rD4Vwc3iVnjZRgBPL3wdpRjScucW5Wbwohp6qZAKR8mM1G2fIp7nLlewIMWM',
                'created_at' => '2023-08-18 09:54:10',
                'updated_at' => '2023-08-18 09:54:10',
            ),
            5 => 
            array (
                'id' => Str::uuid(),
                'name' => 'neraca7102',
                'email' => 'bps7102@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$CSl.ROF7x077zD/PwCaVxe6.qkXlIPaRZaxwRyIRPksrWTYM.3qre',
                'satker_id' => 3,
                'role' => 'user',
                'remember_token' => 'SX88wCTwehExBy7hnC30rA99SZc2cDvsNdxKHFUXF6hN03aESONg9chQDnBZ',
                'created_at' => '2023-08-18 09:54:53',
                'updated_at' => '2023-08-18 09:54:53',
            ),
            6 => 
            array (
                'id' => Str::uuid(),
                'name' => 'neraca7103',
                'email' => 'bps7103@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$JwLtd1ncQ/ZiIWNE8TiHauvi2kSSOTLy22LCAHxb5SpJiSauhVf9.',
                'satker_id' => 4,
                'role' => 'user',
                'remember_token' => 'Z0o18fQHzPLhvYQtk7a0brp3KwVxVHiB2u2nYTVDIMDGqJSRWWjaDD23O9iO',
                'created_at' => '2023-08-18 09:55:28',
                'updated_at' => '2023-08-18 09:55:28',
            ),
            7 => 
            array (
                'id' => Str::uuid(),
                'name' => 'neraca7104',
                'email' => 'bps7104@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$nFOcr9xub3nD67RuT5oRl.LLUgUqD3aUm.vk0AMoAvtSHBXwifWBe',
                'satker_id' => 5,
                'role' => 'user',
                'remember_token' => '4e895XeoZrNoeCirayr4aCEArSlPfEcUuGmodDEeKnJ0lu5uNbPCpzhxAhIk',
                'created_at' => '2023-08-18 09:56:02',
                'updated_at' => '2023-08-18 09:56:02',
            ),
            8 => 
            array (
                'id' => Str::uuid(),
                'name' => 'neraca7105',
                'email' => 'bps7105@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$fnqsOpAyHdyevT6FpKhkFOMug.j8eVeJx1wo8u36a0XzkmnhoCHEy',
                'satker_id' => 6,
                'role' => 'user',
                'remember_token' => 'B61FRn50NaSrER9mHV9NNt6P9K7VLDBiR9fDULfBAY48qnGUup1prspOcMey',
                'created_at' => '2023-08-18 09:56:41',
                'updated_at' => '2023-08-18 09:56:41',
            ),
            9 => 
            array (
                'id' => Str::uuid(),
                'name' => 'neraca7106',
                'email' => 'bps7106@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$DvPEcQl8pqlYCge3pmeBku6hWDD0D20hdihywYrZPWoWwsTe6nzS2',
                'satker_id' => 7,
                'role' => 'user',
                'remember_token' => 'ic7F0WYne18FJMjPD8riwYqsbBUJhYQaHKuqGdUakjZ9KDpKArH7tClS3SfS',
                'created_at' => '2023-08-18 09:57:18',
                'updated_at' => '2023-08-18 09:57:18',
            ),
            10 => 
            array (
                'id' => Str::uuid(),
                'name' => 'neraca7107',
                'email' => 'bps7107@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$AzKj6S.9KMkxDDOyfHbUP.LAGLCgumz82a2ZAWS6vNy.0e9TUEJDm',
                'satker_id' => 8,
                'role' => 'user',
                'remember_token' => 'Vuco6dGS2ew5NzIsZXafuyNIu86lSbFLzDPiw6BoHoFFVHdhe6UgeTVmCSMx',
                'created_at' => '2023-08-18 09:58:04',
                'updated_at' => '2023-08-18 09:58:04',
            ),
            11 => 
            array (
                'id' => Str::uuid(),
                'name' => 'neraca7108',
                'email' => 'bps7108@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$GtHgUXeqwry7lnbglJSmceg4P.9lEfKCuEvgUu6Pg0pynJN9/AEzW',
                'satker_id' => 9,
                'role' => 'user',
                'remember_token' => 'ILnCtKMKPypQWnoAxLqtfEo5d7gZrnFrfgLJ71qKxi1phD1PxKkryVCLJKGN',
                'created_at' => '2023-08-18 09:58:45',
                'updated_at' => '2023-08-18 09:58:45',
            ),
            12 => 
            array (
                'id' => Str::uuid(),
                'name' => 'neraca7111',
                'email' => 'bps7111@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$BISvuU/uOnk23Ddqag5JhulFRFMk6JAAqCE4.54Kbh.MWxmXYPv4e',
                'satker_id' => 14,
                'role' => 'user',
                'remember_token' => 'tEYr5WYvda4fgiMESkOdjCVO8Lj7Qw88nRT51ViBEQnoX8oSRGZ3dyhQ88kv',
                'created_at' => '2023-11-21 11:56:43',
                'updated_at' => '2023-11-21 11:56:43',
            ),
            13 => 
            array (
                'id' => Str::uuid(),
                'name' => 'neraca7171',
                'email' => 'bps7171@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$MCuAdy5seFocREDzviVelehROkzQePzAtm4qJyCmjrubCwOMWkk/G',
                'satker_id' => 10,
                'role' => 'user',
                'remember_token' => 'xrqfQEB3KISBshFC4Vd2gw6RxwMyllTCsAHSwEEY21gUrQhCAwXXPG3oabSg',
                'created_at' => '2023-08-18 09:59:20',
                'updated_at' => '2023-08-18 09:59:20',
            ),
            14 => 
            array (
                'id' => Str::uuid(),
                'name' => 'neraca7172',
                'email' => 'bps7172@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$TU7be/sgLyxPBGEbnedMN.63muT9.UPmoeVDoecuI7IvdSP7b8t5C',
                'satker_id' => 11,
                'role' => 'user',
                'remember_token' => 'g1jMPAHHbR4JqlM9JuQTWJlOFhwbiNJKDARjzcxw3cRYESlgHiOIE2WH11tb',
                'created_at' => '2023-08-18 09:59:58',
                'updated_at' => '2023-08-18 09:59:58',
            ),
            15 => 
            array (
                'id' => Str::uuid(),
                'name' => 'neraca7173',
                'email' => 'bps7173@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$1xxvQMFQ2Qq6OJ.bnFvrUOhPr.zf0S1CVC0JWrukkb7TqzDfUp3h2',
                'satker_id' => 12,
                'role' => 'user',
                'remember_token' => '4p245J7QC6G598R5TWXLvlBK1EbxGr5pKJ92GTjM1pcHLiGTWH7JyKi1hxLL',
                'created_at' => '2023-08-18 10:00:33',
                'updated_at' => '2023-08-18 10:00:33',
            ),
            16 => 
            array (
                'id' => Str::uuid(),
                'name' => 'neraca7174',
                'email' => 'bps7174@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$saE9Fdl0.vIoo5d2IznqbOKNlse8YIGdTUrB7PYSOlSMLswvFsPwm',
                'satker_id' => 13,
                'role' => 'user',
                'remember_token' => 'FiUGmkISm3o1W74b7KgVylelHxBie1octoS7FeMicLdxOgkQX8PSxOHV5eIO',
                'created_at' => '2023-08-18 10:01:01',
                'updated_at' => '2023-08-18 10:01:01',
            ),
            17 => 
            array (
                'id' => Str::uuid(),
                'name' => 'putri',
                'email' => 'putri.sekarsinung@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$YunYUg..YUbvwYjHSyKPq.7WTz9sSJm1tXEJ1PCEDRFTpS0YkwDnq',
                'satker_id' => 1,
                'role' => 'admin',
                'remember_token' => '4WGUXobq3Xy2ttIDrpRc2293YC0Evz14N8DgqRqVGURISSqtCCsSnKbXsbQD',
                'created_at' => '2023-08-18 09:20:59',
                'updated_at' => '2023-08-18 09:20:59',
            ),
            18 => 
            array (
                'id' => Str::uuid(),
                'name' => 'ratna',
                'email' => 'ratnasuli@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$f4vLuvLTHRV4/zosajoJYeMKEdFypAJlSnlMVaBScRRlbUBI4rWm6',
                'satker_id' => 1,
                'role' => 'admin',
                'remember_token' => 'uAkdAK8ai0wiCPWPUomSE07YI4cnITTrrhIYkmRc35rfZnIZxhJYeJWlLpzf',
                'created_at' => '2023-08-18 09:13:33',
                'updated_at' => '2023-08-18 09:13:33',
            ),
            19 => 
            array (
                'id' => Str::uuid(),
                'name' => 'ridwanst',
                'email' => 'ridwanst@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$12$7O8rdyngcKTqWOHDpf/YQeh9s3/y8tDUtKW.f7/PC2WZJJ6c3LQGS',
                'satker_id' => 1,
                'role' => 'admin',
                'remember_token' => 'NozJ63RVOcJsA4qectLMAcxwmFScjwlaCCpLDjqSrKKqUgb3psJzEhG48bxH',
                'created_at' => '2023-07-17 05:00:57',
                'updated_at' => '2025-03-06 06:09:32',
            ),
            20 => 
            array (
                'id' => Str::uuid(),
                'name' => 'sirly',
                'email' => 'sirly@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$CZ3Qc6NA05fOQgoKrveFT.f110CYi/0YR3bhsQIRgXy4LON9acUo2',
                'satker_id' => 1,
                'role' => 'admin',
                'remember_token' => 'emPwq08CTQHjk5idzN7V9ztweAOXWDuw7Zh5uhQrH1a3B9F6RfvjTmW2wJQm',
                'created_at' => '2023-08-21 09:17:26',
                'updated_at' => '2023-08-21 09:17:26',
            ),
            21 => 
            array (
                'id' => Str::uuid(),
                'name' => 'tamu',
                'email' => 'amouratna@yahoo.co.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$07POBeH84zOlAdxF1ylreu9x55SATEBXtPocRDbvbBSmKaQa4Dv26',
                'satker_id' => 1,
                'role' => 'user',
                'remember_token' => '25DxvmjOotNMKU9w4NEN5oQo8c05iKNLC5HJsVtO6CXhh8cqpjIapwVGnJMX',
                'created_at' => '2024-02-23 11:33:41',
                'updated_at' => '2024-02-23 11:33:41',
            ),
            22 => 
            array (
                'id' => Str::uuid(),
                'name' => 'untari',
                'email' => 'untarirahma@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$wukm6F9Omx/kDLt0TD8Ni.panuhna7svk9FnEBhbtDls3LaBuMeYS',
                'satker_id' => 1,
                'role' => 'admin',
                'remember_token' => NULL,
                'created_at' => '2023-08-18 09:18:57',
                'updated_at' => '2023-08-18 09:18:57',
            ),
            23 => 
            array (
                'id' => Str::uuid(),
                'name' => 'zulfa',
                'email' => 'zulfanr@bps.go.id',
                'email_verified_at' => NULL,
                'password' => '$2y$10$uTtrW57PoDTFkfVWrW1N0uMnofZFh6oMZbq37fqX7CKprI49Vxmka',
                'satker_id' => 1,
                'role' => 'admin',
                'remember_token' => 'leAgfBU0NbjKCppFDKmNuc3XXXDAI2gf4ch9h2lJrA9E7IzMQqqnRUI3Hk7n',
                'created_at' => '2023-08-18 09:20:00',
                'updated_at' => '2023-08-18 09:20:00',
            ),
        ));
        
        
    }
}