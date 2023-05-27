
<nav class="navbar navbar-expand-lg navbar-light p-4" style="background-color: #e3f2fd;">
    <a href="{{ url('/') }}" class="navbar-brand">SIN KOLOR</a>

    <ul class="navbar-nav">
        @if(Route::has('login'))
            @auth
                <li class="nav-item">
                    <a href="{{ url('/home') }}" class="nav-link">Home</a>
                </li>
            @else
                <li class="nav-item">
                    <a href="{{ route('login') }}" class="nav-link">Login</a>
                </li>
                @if(Route::has('register'))
                    <li class="nav-item">
                        <a href="{{ route('register') }}" class="nav-link">Register</a>
                    </li>
                @endif
            @endauth
        @endif
    </ul>



</nav>
