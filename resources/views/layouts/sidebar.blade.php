@php 
    $role = auth()->user()->role; 
@endphp

{{-- ================= ADMIN & SUPERADMIN ================= --}}
@if(in_array($role, ['admin', 'superadmin']))

    <a href="{{ route('admin.dashboard') }}" 
       class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2 me-2"></i>
        Dashboard
    </a>

    <a href="{{ route('admin.users.index') }}" 
       class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}">
        <i class="bi bi-people me-2"></i>
        Manajemen User
    </a>

    <a href="{{ route('admin.master.index') }}"
       class="nav-link {{ request()->is('admin/master-data*') ? 'active' : '' }}">
        <i class="bi bi-database me-2"></i>
        Master Data
    </a>

    <a href="{{ route('admin.pengajuan.index') }}" 
       class="nav-link {{ request()->is('admin/pengajuan*') ? 'active' : '' }}">
        <i class="bi bi-file-text me-2"></i>
        Pengajuan
    </a>

    <a href="{{ route('admin.seminar.index') }}"
       class="nav-link {{ request()->is('admin/seminar*') ? 'active' : '' }}">
        <i class="bi bi-calendar-check me-2"></i>
        Seminar Magang
    </a>

    <a href="{{ route('admin.mitra.index') }}" 
       class="nav-link {{ request()->is('admin/mitra*') ? 'active' : '' }}">
        <i class="bi bi-building me-2"></i>
        Data Mitra
    </a>

    <a href="{{ route('admin.periode.index') }}" 
       class="nav-link {{ request()->is('admin/periode*') ? 'active' : '' }}">
        <i class="bi bi-calendar me-2"></i>
        Periode
    </a>

    <a href="{{ route('admin.pedoman.index') }}" 
       class="nav-link {{ request()->is('admin/pedoman*') ? 'active' : '' }}">
        <i class="bi bi-book me-2"></i>
        Pedoman & SOP
    </a>

    <a href="{{ route('admin.laporan.index') }}" 
       class="nav-link {{ request()->is('admin/laporan*') ? 'active' : '' }}">
        <i class="bi bi-bar-chart me-2"></i>
        Laporan
    </a>

    <a href="{{ route('profile.show') }}" 
       class="nav-link {{ request()->is('profile*') ? 'active' : '' }}">
        <i class="bi bi-person me-2"></i>
        Profile
    </a>

{{-- ================= MAHASISWA ================= --}}
@elseif($role === 'mahasiswa')

    <a href="{{ route('mahasiswa.dashboard') }}" 
       class="nav-link {{ request()->is('mahasiswa/dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2 me-2"></i>
        Dashboard
    </a>

    <a href="{{ route('mahasiswa.pengajuan.index') }}" 
       class="nav-link {{ request()->is('mahasiswa/pengajuan*') ? 'active' : '' }}">
        <i class="bi bi-send me-2"></i>
        Pengajuan
    </a>

    <a href="{{ route('mahasiswa.mitra.index') }}"
       class="nav-link {{ request()->is('mahasiswa/mitra*') ? 'active' : '' }}">
        <i class="bi bi-building me-2"></i>
        Daftar Mitra
    </a>

    @php
        $pengajuanAktif = auth()->user()
            ->mahasiswaProfile?->pengajuans()
            ->where('jenis_pengajuan', 'surat_keterangan')
            ->whereIn('status_pengajuan', ['berjalan', 'selesai'])
            ->latest()
            ->first();
    @endphp

    @if($pengajuanAktif)

        <a href="{{ route('mahasiswa.dokumen.index', $pengajuanAktif->id) }}" 
           class="nav-link {{ request()->is('mahasiswa/dokumen*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-arrow-up me-2"></i>
            Dokumen
        </a>

        <a href="{{ route('mahasiswa.logbook.index', $pengajuanAktif->id) }}" 
           class="nav-link {{ request()->is('mahasiswa/logbook*') ? 'active' : '' }}">
            <i class="bi bi-journal-text me-2"></i>
            Logbook
        </a>

        <a href="{{ route('mahasiswa.absensi.index') }}"
           class="nav-link {{ request()->is('mahasiswa/absensi*') ? 'active' : '' }}">
            <i class="bi bi-calendar2-check me-2"></i>
            Absensi Magang
        </a>

        <a href="{{ route('mahasiswa.bimbingan.index') }}"
           class="nav-link {{ request()->is('mahasiswa/bimbingan*') ? 'active' : '' }}">
            <i class="bi bi-chat-dots me-2"></i>
            Bimbingan
        </a>

        <a href="{{ route('mahasiswa.penilaian.show', $pengajuanAktif->id) }}" 
           class="nav-link {{ request()->is('mahasiswa/penilaian*') ? 'active' : '' }}">
            <i class="bi bi-star me-2"></i>
            Nilai
        </a>

    @else

        <a href="{{ route('mahasiswa.dokumen.info') }}"
           class="nav-link {{ request()->is('mahasiswa/dokumen*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-arrow-up me-2"></i>
            Dokumen
        </a>

        <a href="{{ route('mahasiswa.logbook.info') }}"
           class="nav-link {{ request()->is('mahasiswa/logbook*') ? 'active' : '' }}">
            <i class="bi bi-journal-text me-2"></i>
            Logbook
        </a>

        <a href="{{ route('mahasiswa.absensi.index') }}"
           class="nav-link {{ request()->is('mahasiswa/absensi*') ? 'active' : '' }}">
            <i class="bi bi-calendar2-check me-2"></i>
            Absensi Magang
        </a>

        <a href="{{ route('mahasiswa.bimbingan.index') }}"
           class="nav-link {{ request()->is('mahasiswa/bimbingan*') ? 'active' : '' }}">
            <i class="bi bi-chat-dots me-2"></i>
            Bimbingan
        </a>

        <a href="{{ route('mahasiswa.penilaian.info') }}"
           class="nav-link {{ request()->is('mahasiswa/penilaian*') ? 'active' : '' }}">
            <i class="bi bi-star me-2"></i>
            Nilai
        </a>

    @endif

    <a href="{{ route('mahasiswa.seminar.index') }}" 
       class="nav-link {{ request()->is('mahasiswa/seminar*') ? 'active' : '' }}">
        <i class="bi bi-calendar-check me-2"></i>
        Seminar Magang
    </a>

    <a href="{{ route('pedoman.index') }}" 
       class="nav-link {{ request()->is('pedoman*') ? 'active' : '' }}">
        <i class="bi bi-book me-2"></i>
        Pedoman & SOP
    </a>

    <a href="{{ route('profile.show') }}"
       class="nav-link {{ request()->is('profile*') ? 'active' : '' }}">
        <i class="bi bi-person me-2"></i>
        Profile
    </a>
{{-- ================= DOSEN ================= --}}
@elseif($role === 'dosen')

    <a href="{{ route('dosen.dashboard') }}" 
       class="nav-link {{ request()->is('dosen/dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2 me-2"></i>
        Dashboard
    </a>

    <a href="{{ route('dosen.bimbingan.index') }}" 
       class="nav-link {{ request()->is('dosen/bimbingan*') ? 'active' : '' }}">
        <i class="bi bi-mortarboard me-2"></i>
        Bimbingan
    </a>

    <a href="{{ route('dosen.logbook.index') }}"
       class="nav-link {{ request()->is('dosen/logbook*') ? 'active' : '' }}">
        <i class="bi bi-journal-text me-2"></i>
        Logbook
    </a>

    <a href="{{ route('dosen.seminar.index') }}"
       class="nav-link {{ request()->is('dosen/seminar*') ? 'active' : '' }}">
        <i class="bi bi-calendar-check me-2"></i>
        Seminar Magang
    </a>

    <a href="{{ route('dosen.penilaian.index') }}"
       class="nav-link {{ request()->is('dosen/penilaian*') ? 'active' : '' }}">
        <i class="bi bi-star me-2"></i>
        Penilaian
    </a>

    <a href="{{ route('profile.show') }}" 
       class="nav-link {{ request()->is('profile*') ? 'active' : '' }}">
        <i class="bi bi-person me-2"></i>
        Profile
    </a>

    <a href="{{ route('pedoman.index') }}" 
       class="nav-link {{ request()->is('pedoman*') ? 'active' : '' }}">
        <i class="bi bi-book me-2"></i>
        Pedoman & SOP
    </a>

{{-- ================= PEMBIMBING LAPANGAN ================= --}}
@elseif($role === 'pembimbing_lapangan')

    <a href="{{ route('pembimbing.dashboard') }}"
       class="nav-link {{ request()->is('pembimbing-lapangan/dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2 me-2"></i>
        Dashboard
    </a>

    <a href="{{ route('pembimbing.mahasiswa.index') }}"
       class="nav-link {{ request()->is('pembimbing-lapangan/mahasiswa*') ? 'active' : '' }}">
        <i class="bi bi-people me-2"></i>
        Mahasiswa Bimbingan
    </a>

    <a href="{{ route('pembimbing.absensi.index') }}"
       class="nav-link {{ request()->is('pembimbing-lapangan/absensi*') ? 'active' : '' }}">
        <i class="bi bi-calendar2-check me-2"></i>
        Absensi Mahasiswa
    </a>

    <a href="{{ route('pembimbing.logbook.index') }}"
       class="nav-link {{ request()->is('pembimbing-lapangan/logbook*') ? 'active' : '' }}">
        <i class="bi bi-journal-text me-2"></i>
        Logbook Mahasiswa
    </a>

    <a href="{{ route('pembimbing.penilaian.index') }}"
       class="nav-link {{ request()->is('pembimbing-lapangan/penilaian*') ? 'active' : '' }}">
        <i class="bi bi-star me-2"></i>
        Penilaian Lapangan
    </a>
    <a href="{{ route('pembimbing.seminar.index') }}"
       class="nav-link {{ request()->is('pembimbing-lapangan/seminar*') ? 'active' : '' }}">
        <i class="bi bi-calendar-check me-2"></i>
        Seminar Magang
    </a>

    <a href="{{ route('pedoman.index') }}"
       class="nav-link {{ request()->is('pedoman*') ? 'active' : '' }}">
        <i class="bi bi-book me-2"></i>
        Pedoman & SOP
    </a>

    <a href="{{ route('profile.show') }}"
       class="nav-link {{ request()->is('profile*') ? 'active' : '' }}">
        <i class="bi bi-person me-2"></i>
        Profile
    </a>
{{-- ================= MITRA ================= --}}
@elseif($role === 'mitra')

    <a href="{{ route('mitra.dashboard') }}" 
       class="nav-link {{ request()->is('mitra/dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2 me-2"></i>
        Dashboard
    </a>

    <a href="{{ route('mitra.pengajuan.index') }}" 
       class="nav-link {{ request()->is('mitra/pengajuan*') ? 'active' : '' }}">
        <i class="bi bi-people me-2"></i>
        Mahasiswa Magang
    </a>

    <a href="{{ route('mitra.pic.index') }}"
       class="nav-link {{ request()->is('mitra/pic*') ? 'active' : '' }}">
        <i class="bi bi-person-badge me-2"></i>
        Pembimbing Lapangan/PIC
    </a>

    <a href="{{ route('mitra.absensi.index') }}"
       class="nav-link {{ request()->is('mitra/absensi*') ? 'active' : '' }}">
        <i class="bi bi-calendar2-check me-2"></i>
        Absensi Mahasiswa
    </a>

    <a href="{{ route('mitra.logbook.index') }}"
       class="nav-link {{ request()->is('mitra/logbook*') ? 'active' : '' }}">
        <i class="bi bi-journal-text me-2"></i>
        Logbook
    </a>

    <a href="{{ route('mitra.penilaian.index') }}"
       class="nav-link {{ request()->is('mitra/penilaian*') ? 'active' : '' }}">
        <i class="bi bi-star me-2"></i>
        Penilaian Lapangan
    </a>

    <a href="{{ route('pedoman.index') }}" 
       class="nav-link {{ request()->is('pedoman*') ? 'active' : '' }}">
        <i class="bi bi-book me-2"></i>
        Pedoman & SOP
    </a>

    <a href="{{ route('profile.show') }}" 
       class="nav-link {{ request()->is('profile*') ? 'active' : '' }}">
        <i class="bi bi-person me-2"></i>
        Profile
    </a>

@endif

