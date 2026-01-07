<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-bold mb-4">Pendaftaran Tukang</h2>
    <form action="{{ route('admin.registerTukang') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block font-semibold mb-1">Foto Profil</label>
                    <input type="file" name="profile_photo" accept="image/*" class="border rounded px-3 py-2 w-full">
                </div>
        @csrf
        <div>
            <label class="block font-semibold mb-1">Nama</label>
            <input type="text" name="name" class="border rounded px-3 py-2 w-full" required>
        </div>
        <div>
            <label class="block font-semibold mb-1">Email</label>
            <input type="email" name="email" class="border rounded px-3 py-2 w-full" required>
        </div>
        <div>
            <label class="block font-semibold mb-1">Alamat</label>
            <input type="text" name="alamat" class="border rounded px-3 py-2 w-full">
        </div>
        <div>
            <label class="block font-semibold mb-1">Nomor Telepon</label>
            <input type="text" name="nomor_telepon" class="border rounded px-3 py-2 w-full">
        </div>
        <div>
            <label class="block font-semibold mb-1">Password</label>
            <input type="password" name="password" class="border rounded px-3 py-2 w-full" required>
        </div>
        <input type="hidden" name="role" value="tukang">
        <button type="submit" class="bg-[#f6c90e] text-[#222b45] font-bold px-6 py-2 rounded shadow hover:bg-yellow-400 transition">Daftarkan Tukang</button>
    </form>
</div>
