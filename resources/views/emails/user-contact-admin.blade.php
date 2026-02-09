<h3>Pesan Dari User</h3>

<p><strong>Nama :</strong> {{ $name }}</p>
<p><strong>Email :</strong> {{ $email }}</p>

<hr>

<p><strong>Pesan :</strong> {{ $message }}</p>

@if ($attachmentPath)
    <p><strong>Lampiran :</strong> <a href="{{ $attachmentPath }}">Lihat Lampiran</a></p>
@endif