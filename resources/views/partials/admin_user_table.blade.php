<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-bold mb-4">Data User</h2>
    <div class="mb-4">
        <input type="text" id="search-user" placeholder="Cari user..." class="border rounded px-3 py-2 w-full" />
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="px-4 py-2 bg-white text-gray-800 font-semibold border-b text-center">Nama</th>
                    <th class="px-4 py-2 bg-white text-gray-800 font-semibold border-b text-center">Email</th>
                    <th class="px-4 py-2 bg-white text-gray-800 font-semibold border-b">Jumlah Tong</th>
                    <th class="px-4 py-2 bg-white text-gray-800 font-semibold border-b">Status</th>
                    <th class="px-4 py-2 bg-white text-gray-800 font-semibold border-b">Kadar Gas</th>
                    <th class="px-4 py-2 bg-white text-gray-800 font-semibold border-b">Presentase Sampah</th>
                    <th class="px-4 py-2 bg-white text-gray-800 font-semibold border-b">Cleaning Status</th>
                </tr>
            </thead>
            <tbody id="user-table-body">
                @forelse($users as $user)
                    <tr>
                        <td class="px-4 py-2 border-b text-center">{{ $user->name }}</td>
                        <td class="px-4 py-2 border-b text-center">{{ $user->email }}</td>
                        <td class="px-4 py-2 border-b text-center">{{ $user->devices->count() }}</td>
                        <td class="px-4 py-2 border-b text-center">
                            @php
                                $online = $user->devices->where('status', 'online');
                                $offline = $user->devices->where('status', 'offline');
                                $pending = $user->devices->where('status', 'pending');
                                $onlineCount = $online->count();
                                $offlineCount = $offline->count();
                                $pendingCount = $pending->count();
                                $onlineNames = $online->pluck('nama_device')->filter()->implode(', ');
                                $offlineNames = $offline->pluck('nama_device')->filter()->implode(', ');
                                $pendingNames = $pending->pluck('nama_device')->filter()->implode(', ');
                            @endphp
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-green-600 font-semibold" title="@if($onlineCount>0)Online: {{ $onlineNames }}@endif">Online: {{ $onlineCount }}</span>
                                @if($offlineCount > 0)
                                    <span class="text-red-600 font-semibold" title="@if($offlineCount>0)Offline: {{ $offlineNames }}@endif">Offline: {{ $offlineCount }}</span>
                                @endif
                                @if($pendingCount > 0)
                                    <span class="text-yellow-500 font-semibold" title="@if($pendingCount>0)Pending: {{ $pendingNames }}@endif">Pending: {{ $pendingCount }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-2 border-b text-center">
                            @if($user->devices->count() > 0)
                                <ul>
                                @foreach($user->devices as $device)
                                    <li>
                                        <span class="font-semibold">{{ $device->nama_device ?? 'Device' }}</span>:
                                        @if($device->latestReading)
                                            {{ $device->latestReading->gas ?? '-' }}
                                        @else
                                            -
                                        @endif
                                    </li>
                                @endforeach
                                </ul>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-2 border-b text-center">
                            @if($user->devices->count() > 0)
                                <ul>
                                @foreach($user->devices as $device)
                                    <li>
                                        <span class="font-semibold">{{ $device->nama_device ?? 'Device' }}</span>:
                                        @if($device->latestReading)
                                            {{ $device->latestReading->volume ?? '-' }}%
                                        @else
                                            -
                                        @endif
                                    </li>
                                @endforeach
                                </ul>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-2 border-b text-center">
                            @if($user->devices->count() > 0)
                                <ul>
                                @foreach($user->devices as $device)
                                    <li>
                                        <span class="font-semibold">{{ $device->nama_device ?? 'Device' }}</span>:
                                        @if(isset($device->cleaning_status))
                                            @if($device->cleaning_status === 'sudah')
                                                <span class="text-green-600 font-semibold">Sudah</span>
                                            @else
                                                <span class="text-red-600 font-semibold">Belum</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </li>
                                @endforeach
                                </ul>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-gray-400">Tidak ada data user.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
<script>
document.getElementById('search-user')?.addEventListener('input', function() {
    const search = this.value.toLowerCase();
    document.querySelectorAll('#user-table-body tr').forEach(function(row) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    });
});
</script>
</div>
