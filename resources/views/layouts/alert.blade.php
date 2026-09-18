<div class="alert-wrapper">
    {{-- Success Alert --}}
    @if (session('success'))
        <div class="alert alert-success">
            <div class="alert-content">
                <span class="alert-icon">✓</span>
                <div class="alert-message">
                    {{ session('success') }}
                </div>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    {{-- Danger / Error Alert --}}
    @if (session('error'))
        <div class="alert alert-danger">
            <div class="alert-content">
                <span class="alert-icon">✕</span>
                <div class="alert-message">
                    {{ session('error') }}
                </div>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    {{-- Warning Alert --}}
    @if (session('warning'))
        <div class="alert alert-warning">
            <div class="alert-content">
                <span class="alert-icon">⚠️</span>
                <div class="alert-message">
                    {{ session('warning') }}
                </div>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    {{-- Info Alert --}}
    @if (session('info'))
        <div class="alert alert-info">
            <div class="alert-content">
                <span class="alert-icon">ℹ️</span>
                <div class="alert-message">
                    {{ session('info') }}
                </div>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    {{-- Form Validation Errors Alert --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="alert-content">
                <span class="alert-icon">⚠️</span>
                <div class="alert-message">
                    <strong>Terdapat beberapa kesalahan:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif
</div>
