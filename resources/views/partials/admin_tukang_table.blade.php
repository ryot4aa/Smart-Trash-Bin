<div class="rounded-lg shadow p-6">
    <h2 class="text-lg font-bold mb-4 text-black">Data Tukang</h2>
    <div class="mb-4">
        <input type="text" id="search-tukang" placeholder="Cari tukang..." class="border rounded px-3 py-2 w-full" />
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-[#f6c90e] font-bold border-b text-center">Nama</th>
                    <th class="px-4 py-2 text-[#f6c90e] font-bold border-b text-center">Email</th>
                    <th class="px-4 py-2 text-[#f6c90e] font-bold border-b text-center">No Telepon</th>
                    <th class="px-4 py-2 text-[#f6c90e] font-bold border-b text-center">Alamat</th>
                    <th class="px-4 py-2 text-[#f6c90e] font-bold border-b text-center">Foto</th>
                </tr>
            </thead>
            <tbody id="tukang-table-body">
                @forelse($tukangs as $tukang)
                    <tr>
                        <td class="px-4 py-2 border-b text-center">{{ $tukang->name }}</td>
                        <td class="px-4 py-2 border-b text-center">{{ $tukang->email }}</td>
                        <td class="px-4 py-2 border-b text-center">{{ $tukang->nomor_telepon ?? '-' }}</td>
                        <td class="px-4 py-2 border-b text-center">{{ $tukang->alamat ?? '-' }}</td>
                        <td class="px-4 py-2 border-b text-center">
                            @if($tukang->profile_photo)
                                <img src="{{ asset('storage/' . $tukang->profile_photo) }}" alt="Foto" class="h-12 w-12 rounded-full object-cover mx-auto">
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <!-- Kolom aksi dan status bersih dihapus, kembali ke tampilan awal -->
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-gray-400">Tidak ada data tukang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
<script>
document.getElementById('search-tukang')?.addEventListener('input', function() {
    const search = this.value.toLowerCase();
    document.querySelectorAll('#tukang-table-body tr').forEach(function(row) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    });
});
</script>
</div>
