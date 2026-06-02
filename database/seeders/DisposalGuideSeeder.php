<?php

namespace Database\Seeders;

use App\Models\DisposalGuide;
use Illuminate\Database\Seeder;

class DisposalGuideSeeder extends Seeder
{
    public function run(): void
    {
        $guides = [
            [
                'medicine_form' => 'tablet',
                'title'         => 'Pembuangan Obat Tablet & Kapsul',
                'description'   => 'Tablet dan kapsul harus dihancurkan terlebih dahulu sebelum dibuang agar tidak disalahgunakan atau mencemari air tanah.',
                'steps'         => [
                    'Keluarkan tablet dari kemasan blister atau botol.',
                    'Hancurkan tablet menggunakan sendok atau mortar hingga berbentuk serbuk.',
                    'Campur serbuk dengan bahan tidak diinginkan seperti tanah, ampas kopi, atau bubuk cabai.',
                    'Masukkan campuran ke dalam kantong plastik tertutup rapat.',
                    'Buang ke tempat sampah umum (bukan recycle bin).',
                    'Kembalikan kemasan kosong dan blister ke apotek terdekat bila memungkinkan.',
                ],
                'icon'     => 'pill',
                'color'    => '#1D9E75',
                'is_active'=> true,
            ],
            [
                'medicine_form' => 'sirup',
                'title'         => 'Pembuangan Obat Sirup & Cairan',
                'description'   => 'Obat cair tidak boleh disiramkan langsung ke saluran pembuangan tanpa diencerkan karena dapat mencemari sumber air.',
                'steps'         => [
                    'Buka tutup botol sirup dengan hati-hati.',
                    'Campurkan cairan obat dengan air dalam jumlah banyak (rasio 1:10).',
                    'Tambahkan bahan pengikat seperti tanah atau cat bekas untuk melarutkan zat aktif.',
                    'Tuangkan campuran ke toilet dan siram (hanya jika tidak ada opsi apotek).',
                    'Tutup botol kosong dengan lakban, lalu buang ke tempat sampah umum.',
                    'Opsi terbaik: kembalikan ke apotek atau titik pengumpulan obat.',
                ],
                'icon'     => 'flask-conical',
                'color'    => '#185FA5',
                'is_active'=> true,
            ],
            [
                'medicine_form' => 'krim',
                'title'         => 'Pembuangan Krim, Salep & Gel',
                'description'   => 'Krim dan salep mengandung bahan aktif yang perlu dinonaktifkan sebelum dibuang ke lingkungan.',
                'steps'         => [
                    'Gunakan spatula atau sendok untuk mengosongkan tube/pot krim.',
                    'Campur krim dengan bahan absorben seperti pasir kucing, tanah, atau tepung.',
                    'Tutup campuran dalam wadah tertutup rapat (toples atau kantong plastik tebal).',
                    'Buang ke tempat sampah umum.',
                    'Gunting atau rusak kemasan tube sebelum dibuang agar tidak digunakan ulang.',
                    'Hindari membuang ke saluran air.',
                ],
                'icon'     => 'droplets',
                'color'    => '#7F77DD',
                'is_active'=> true,
            ],
            [
                'medicine_form' => 'injeksi',
                'title'         => 'Pembuangan Jarum Suntik & Ampul',
                'description'   => 'Benda tajam medis harus ditangani khusus untuk mencegah cedera dan kontaminasi. Jangan pernah membuang jarum sembarangan.',
                'steps'         => [
                    'JANGAN menutup kembali tutup jarum dengan tangan — gunakan teknik satu tangan.',
                    'Masukkan jarum ke dalam wadah benda tajam (sharps container) yang kokoh.',
                    'Isi wadah maksimal ¾ kapasitas, kemudian tutup rapat.',
                    'Bawa ke fasilitas kesehatan, apotek, atau puskesmas untuk pembuangan aman.',
                    'Untuk ampul: bungkus dengan kertas tebal sebelum dimasukkan ke wadah benda tajam.',
                    'Jangan membuang jarum ke tempat sampah umum atau toilet.',
                ],
                'icon'     => 'syringe',
                'color'    => '#E24B4A',
                'is_active'=> true,
            ],
            [
                'medicine_form' => 'inhaler',
                'title'         => 'Pembuangan Inhaler & Aerosol',
                'description'   => 'Inhaler mengandung gas bertekanan yang mudah terbakar dan dapat meledak jika tidak ditangani dengan benar.',
                'steps'         => [
                    'Pastikan inhaler benar-benar kosong dengan menekan beberapa kali.',
                    'JANGAN menusuk, membakar, atau menghancurkan tabung inhaler.',
                    'Lepaskan bagian plastik dari tabung aluminium jika memungkinkan.',
                    'Buang tabung kosong ke tempat sampah umum (bukan api atau tempat panas).',
                    'Hubungi apotek untuk program take-back inhaler bila tersedia.',
                    'Cek logo daur ulang pada kemasan — beberapa inhaler dapat didaur ulang.',
                ],
                'icon'     => 'wind',
                'color'    => '#EF9F27',
                'is_active'=> true,
            ],
            [
                'medicine_form' => 'tetes',
                'title'         => 'Pembuangan Obat Tetes (Mata, Telinga, Hidung)',
                'description'   => 'Obat tetes biasanya tersedia dalam botol kecil dan harus dibuang dengan cara yang aman.',
                'steps'         => [
                    'Kosongkan sisa cairan dengan mencampurnya dengan bahan absorben (pasir atau tanah).',
                    'Tutup rapat botol setelah dikosongkan.',
                    'Buang botol ke tempat sampah umum.',
                    'Untuk obat tetes yang mengandung bahan keras (seperti antibiotik): konsultasikan ke apotek.',
                    'Jangan membuang langsung ke saluran air atau tanah terbuka.',
                ],
                'icon'     => 'eye',
                'color'    => '#1D9E75',
                'is_active'=> true,
            ],
            [
                'medicine_form' => 'suppositoria',
                'title'         => 'Pembuangan Supositoria & Ovula',
                'description'   => 'Supositoria mengandung bahan aktif yang harus diinaktivasi sebelum pembuangan.',
                'steps'         => [
                    'Keluarkan supositoria dari kemasan foil atau plastik.',
                    'Hancurkan supositoria dan campur dengan tanah atau pasir.',
                    'Masukkan campuran ke dalam kantong plastik tertutup.',
                    'Buang ke tempat sampah umum.',
                    'Buang kemasan foil terpisah ke tempat sampah umum.',
                ],
                'icon'     => 'package',
                'color'    => '#EF9F27',
                'is_active'=> true,
            ],
            [
                'medicine_form' => 'plester',
                'title'         => 'Pembuangan Plester Obat (Patch Transdermal)',
                'description'   => 'Plester obat masih mengandung bahan aktif bahkan setelah digunakan dan dapat berbahaya bagi orang lain.',
                'steps'         => [
                    'Lipat plester bekas ke dalam sehingga sisi lengket bertemu sisi lengket.',
                    'JANGAN membiarkan plester bekas terjangkau anak-anak atau hewan peliharaan.',
                    'Masukkan ke dalam wadah yang tidak dapat dibuka oleh anak-anak.',
                    'Buang ke tempat sampah umum.',
                    'Cuci tangan setelah menangani plester bekas.',
                    'Jangan menyiram plester ke toilet.',
                ],
                'icon'     => 'bandage',
                'color'    => '#185FA5',
                'is_active'=> true,
            ],
        ];

        foreach ($guides as $guide) {
            DisposalGuide::updateOrCreate(
                ['medicine_form' => $guide['medicine_form']],
                $guide
            );
        }

        $this->command->info('✅ DisposalGuideSeeder: ' . count($guides) . ' panduan pembuangan berhasil di-seed.');
    }
}
