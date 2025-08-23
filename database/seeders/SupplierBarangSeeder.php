<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierBarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data lama dengan urutan yang benar (child dulu, baru parent)
        // Urutan penghapusan harus mengikuti dependensi foreign key
        DB::statement('SET FOREIGN_KEY_CHECKS=0'); // Nonaktifkan foreign key sementara
        
        // Hapus data dari tabel yang memiliki foreign key ke barangs (dan tabel lain yang terkait)
        DB::table('laporans')->truncate();
        DB::table('transaksi_penjualans')->truncate();
        DB::table('pemesanans')->truncate();
        
        // Baru hapus data barangs dan suppliers
        DB::table('barangs')->truncate();
        DB::table('suppliers')->truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1'); // Aktifkan kembali foreign key
        
        // Buat supplier
        $supplier1 = Supplier::create([
            'nama_supplier' => 'PT. Elektronik Maju Jaya',
            'alamat' => 'Jl. Sudirman No. 123, Jakarta Pusat',
            'kontak' => '0812-3456-7890',
        ]);
        
        $supplier2 = Supplier::create([
            'nama_supplier' => 'CV. Gadget Berkualitas',
            'alamat' => 'Jl. Thamrin No. 456, Surabaya',
            'kontak' => '0857-8901-2345',
        ]);
        
        $supplier3 = Supplier::create([
            'nama_supplier' => 'Distributor Komputer Bandung',
            'alamat' => 'Jl. Diponegoro No. 789, Bandung',
            'kontak' => '0878-1234-5678',
        ]);
        
        $supplier4 = Supplier::create([
            'nama_supplier' => 'PT. Teknologi Digital Nusantara',
            'alamat' => 'Jl. Gatot Subroto No. 101, Medan',
            'kontak' => '0821-2345-6789',
        ]);
        
        $supplier5 = Supplier::create([
            'nama_supplier' => 'Elektronik Murah Semarang',
            'alamat' => 'Jl. Pemuda No. 202, Semarang',
            'kontak' => '0813-3456-7890',
        ]);
        
        $this->command->info('Supplier created successfully!');
        
        // Buat barang untuk supplier 1 (PT. Elektronik Maju Jaya)
        $this->createBarangForSupplier($supplier1, [
            ['Smartphone Samsung Galaxy S24', 50, 15999000.00, 5],
            ['Laptop Dell Inspiron 15', 30, 12500000.00, 3],
            ['Smart TV LG 55 inch', 20, 8999000.00, 2],
            ['Headphone Wireless Sony', 40, 2499000.00, 5],
            ['Power Bank 20000mAh', 60, 599000.00, 10],
        ]);
        
        // Buat barang untuk supplier 2 (CV. Gadget Berkualitas)
        $this->createBarangForSupplier($supplier2, [
            ['Smartphone Xiaomi Redmi Note 13', 45, 3499000.00, 5],
            ['Smartwatch Apple Watch Series 9', 25, 6499000.00, 3],
            ['Earphone Bluetooth', 70, 799000.00, 10],
            ['Kabel Charger USB-C', 100, 149000.00, 20],
            ['Smart Speaker Google Nest', 30, 1799000.00, 5],
        ]);
        
        // Buat barang untuk supplier 3 (Distributor Komputer Bandung)
        $this->createBarangForSupplier($supplier3, [
            ['Laptop Lenovo ThinkPad', 25, 14500000.00, 3],
            ['Monitor Dell 24 inch', 35, 3299000.00, 5],
            ['Keyboard Mechanical', 50, 899000.00, 10],
            ['Mouse Gaming Logitech', 60, 699000.00, 10],
            ['Printer HP Deskjet', 20, 1599000.00, 3],
        ]);
        
        // Buat barang untuk supplier 4 (PT. Teknologi Digital Nusantara)
        $this->createBarangForSupplier($supplier4, [
            ['Smartphone iPhone 15 Pro', 30, 18999000.00, 5],
            ['Tablet Samsung Tab S9', 25, 12999000.00, 3],
            ['Kamera DSLR Canon EOS 2000D', 15, 7499000.00, 2],
            ['Lensa Kamera 50mm', 20, 2499000.00, 3],
            ['Tripod Kamera', 30, 899000.00, 5],
        ]);
        
        // Buat barang untuk supplier 5 (Elektronik Murah Semarang)
        $this->createBarangForSupplier($supplier5, [
            ['TV LED Polytron 32 inch', 40, 2999000.00, 5],
            ['Rice Cooker Miyako', 50, 599000.00, 10],
            ['Blender Cosmos', 35, 399000.00, 8],
            ['Kipas Angin Maspion', 45, 299000.00, 10],
            ['Setrika Philips', 40, 499000.00, 8],
        ]);
        
        $this->command->info('Barang created successfully!');
    }
    
    private function createBarangForSupplier(Supplier $supplier, array $barangData)
    {
        foreach ($barangData as $data) {
            Barang::create([
                'nama_barang' => $data[0],
                'stok' => $data[1],
                'harga' => $data[2], // Format harga dengan dua angka desimal (contoh: 5000.00)
                'level_minimum' => $data[3],
                'supplier_id' => $supplier->id,
            ]);
        }
    }
}
