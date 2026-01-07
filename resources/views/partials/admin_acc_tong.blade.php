<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-bold mb-4">ACC Tong (Persetujuan Tong Sampah Baru)</h2>
    <table class="min-w-full text-center">
        <thead>
            <tr>
                <th class="px-4 py-2">ID</th>
                <th class="px-4 py-2">Nama Tong</th>
                <th class="px-4 py-2">Lokasi</th>
                <th class="px-4 py-2">User</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach(App\Models\Device::where('status','pending')->with('user')->get() as $device)
            <tr>
                <td class="px-4 py-2">{{ $device->id }}</td>
                <td class="px-4 py-2">{{ $device->nama_device }}</td>
                <td class="px-4 py-2">{{ $device->lokasi }}</td>
                <td class="px-4 py-2">{{ $device->user->name ?? '-' }}</td>
                <td class="px-4 py-2"><span class="text-yellow-500 font-semibold">Pending</span></td>
                <td class="px-4 py-2">
                    <form action="/admin/acc-tong/{{ $device->id }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" name="action" value="acc" class="bg-green-500 text-white px-3 py-1 rounded shadow hover:bg-green-600">ACC</button>
                        <button type="submit" name="action" value="tolak" class="bg-red-500 text-white px-3 py-1 rounded shadow hover:bg-red-600 ml-2">Tolak</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @if(App\Models\Device::where('status','pending')->count() == 0)
        <div class="text-center text-gray-500 mt-6">Tidak ada pengajuan tong baru.</div>
    @endif
</div>
