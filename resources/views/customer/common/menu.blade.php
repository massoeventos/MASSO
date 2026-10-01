<aside class="left-sidebar">
    <div class="scroll-sidebar">

        <nav class="sidebar-nav">
            <ul id="sidebarnav">
                <li class="user-pro"> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                    <i class="fa fa-user"></i>
                    <span class="hide-menu">{{ Auth::guard('customer')->user()->name ?: Auth::guard('customer')->user()->email }}</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li>
                            <form method="POST" action="{{ route('customer.logout') }}">
                                @csrf
                                <button type="submit" class="btn-link" style="border:0; background:none; padding:0; text-align:left; width:100%;"><i class="fa fa-power-off"></i> Cerrar Sesión</button>
                            </form>
                        </li>
                    </ul>
                </li>
                <li>
                    <a class="waves-effect waves-dark @if( in_array($currentRoute, ['customer.account']) ) active @endif" href="{{ route('customer.account') }}" aria-expanded="false">
                        <i class="fa fa-ticket-alt"></i><span class="hide-menu">Mis Compras</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect waves-dark @if( in_array($currentRoute, ['customer.profile']) ) active @endif" href="{{ route('customer.profile') }}" aria-expanded="false">
                        <i class="fa fa-id-card"></i><span class="hide-menu">Mi Perfil</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
