<div class="bg-white rounded-2xl shadow p-6">
    <h2 class="text-xl font-semibold mb-4 text-[#f6c90e] text-center">Daftar Tong Sampah (Admin)</h2>
    <div class="mb-4">
        <input type="text" id="search-admin-device" placeholder="Cari device atau pemilik..." class="border rounded px-3 py-2 w-full" />
    </div>
    <div class="overflow-x-auto max-h-96">
        <table class="w-full bg-white rounded-lg overflow-hidden">
            <thead class="bg-[#f6c90e] sticky top-0">
                <tr>
                    <th class="py-2 px-4 text-left text-xs font-bold text-[#222b45] uppercase">ID</th>
                    <th class="py-2 px-4 text-left text-xs font-bold text-[#222b45] uppercase">Nama Tong</th>
                    <th class="py-2 px-4 text-left text-xs font-bold text-[#222b45] uppercase">Pemilik</th>
                    <th class="py-2 px-4 text-left text-xs font-bold text-[#222b45] uppercase">Lokasi</th>
                    <th class="py-2 px-4 text-left text-xs font-bold text-[#222b45] uppercase">Status</th>
                    <th class="py-2 px-4 text-left text-xs font-bold text-[#222b45] uppercase">Gas</th>
                    <th class="py-2 px-4 text-left text-xs font-bold text-[#222b45] uppercase">Sampah %</th>
                    <th class="py-2 px-4 text-left text-xs font-bold text-[#222b45] uppercase">Clean</th>
                </tr>
            </thead>
            <tbody id="admin-device-table-body" class="divide-y divide-gray-200">
                @php $hasDevice = false; @endphp
                @foreach($users as $user)
                    @foreach($user->devices as $device)
                        @php $hasDevice = true; @endphp
                        <tr>
                            <td class="py-2 px-4">{{ $device->id }}</td>
                            <td class="py-2 px-4">{{ $device->nama_device ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $user->name }}</td>
                            <td class="py-2 px-4">{{ $device->lokasi ?? '-' }}</td>
                            <td class="py-2 px-4">
                                @if($device->status === 'online')
                                    <span class="text-green-600 font-semibold">Online</span>
                                @elseif($device->status === 'pending')
                                    <span class="text-yellow-500 font-semibold">Pending</span>
                                @else
                                    <span class="text-red-600 font-semibold">Offline</span>
                                @endif
                            </td>
                            <td class="py-2 px-4">{{ $device->latestReading?->gas ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $device->latestReading?->volume ? $device->latestReading->volume.'%' : '-' }}</td>
                            <td class="py-2 px-4">
                                @if($device->cleaning_status === 'sudah')
                                    <span class="text-green-600 font-semibold">Sudah</span>
                                @else
                                    <span class="text-red-600 font-semibold">Belum</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                @unless($hasDevice)
                    <tr>
                        <td colspan="8" class="text-center py-4 text-gray-400">Tidak ada device.</td>
                    </tr>
                @endunless
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('search-admin-device')?.addEventListener('input', function() {
    const search = this.value.toLowerCase();
    document.querySelectorAll('#admin-device-table-body tr').forEach(function(row) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    });
});
</script>
