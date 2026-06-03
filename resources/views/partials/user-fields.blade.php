@php
    $userValue = fn ($field, $fallback = '') => old($field, $managedUser?->{$field} ?? $fallback);
@endphp

<div>
    <label class="label">Nama</label>
    <input class="field" name="name" value="{{ $userValue('name') }}" required>
</div>
<div>
    <label class="label">Email</label>
    <input class="field" name="email" type="email" value="{{ $userValue('email') }}" required>
</div>
<div>
    <label class="label">Role</label>
    <select class="field" name="role" required>
        @foreach (['superadmin' => 'Superadmin', 'surveyor' => 'Surveyor', 'verifikator' => 'Verifikator', 'user' => 'User'] as $value => $text)
            <option value="{{ $value }}" @selected($userValue('role', 'user') === $value)>{{ $text }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="label">Telepon</label>
    <input class="field" name="phone" value="{{ $userValue('phone') }}">
</div>
<div>
    <label class="label">Password {{ $managedUser ? '(kosongkan jika tetap)' : '' }}</label>
    <input class="field" name="password" type="password" @required(! $managedUser)>
</div>
<div>
    <label class="label">Alamat</label>
    <input class="field" name="address" value="{{ $userValue('address') }}">
</div>
