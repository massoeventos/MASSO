<aside class="left-sidebar">
    <div class="scroll-sidebar">

        <nav class="sidebar-nav">
            <ul id="sidebarnav">
                <li class="user-pro"> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                    <i class="fa fa-user"></i>
                    <span class="hide-menu">{{ $authUser->getName() }}</span></a>
                    <ul aria-expanded="false" class="collapse">
                        <li><a href="{{ route('logout.index') }}"><i class="fa fa-power-off"></i> Cerrar Sesión</a></li>
                    </ul>
                </li>
                <li>
                    <a class="waves-effect waves-dark @if( in_array($croute, ['dashboard.index']) ) active @endif" href="{{ route('dashboard.index') }}" aria-expanded="false">
                        <i class="fa fa-tachometer-alt"></i><span class="hide-menu">Inicio</span>
                    </a>
                </li>
                @if( $authUser->canDo('dashboard.index|files.index|files.edit|files.create|payments.index|payments.edit|payments.create|clients.index|clients.edit|clients.create') ) 
                <li class="nav-small-cap">--- Panel de Control</li>
                <li>
                    <a class="waves-effect waves-dark @if( in_array($croute, ['events.index','events.edit','events.create','files.index','files.edit','files.create','enrolls.index', 'enrolls.show']) ) active @endif" href="{{ route('events.index') }}" aria-expanded="false">
                        <i class="fa fa-calendar-check"></i><span class="hide-menu">Eventos</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect waves-dark @if( in_array($croute, ['events.expired']) ) active @endif" href="{{ route('events.expired') }}" aria-expanded="false">
                        <i class="fa fa-calendar-minus"></i><span class="hide-menu">Eventos Expirados</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect waves-dark @if( in_array($croute, ['payments.index','payments.edit','payments.create', 'payments.show']) ) active @endif" href="{{ route('payments.index') }}" aria-expanded="false">
                        <i class="fa fa-money-bill-alt"></i><span class="hide-menu">Pagos</span>
                    </a>
                </li>
                <li>
                    <a class="waves-effect waves-dark @if( in_array($croute, ['surveys.index','surveys.edit','surveys.create']) ) active @endif" href="{{ route('surveys.index') }}" aria-expanded="false">
                        <i class="mdi-poll mdi"></i><span class="hide-menu">Respuestas de Encuesta</span>
                    </a>
                </li>

                <li>
                    <a class="waves-effect waves-dark @if( in_array($croute, ['clients.index','clients.edit','clients.create']) ) active @endif" href="{{ route('clients.index') }}" aria-expanded="false">
                        <i class="fa fa-users"></i><span class="hide-menu">Asistentes Histórico</span>
                    </a>
                </li>

                <li>
                    <a class="waves-effect waves-dark @if( in_array($croute, ['team.index','team.edit','team.create']) ) active @endif" href="{{ route('team.index') }}" aria-expanded="false">
                        <i class="fa fa-user-plus"></i><span class="hide-menu">Equipo de Trabajo</span>
                    </a>
                </li>
                
                
                @endif



                @if( $authUser->canDo('g.admin.index') ) 
                    <li class="nav-small-cap">--- Administración</li>
                    <li class="@if( in_array($croute, ['g.admin.index']) ) active @endif"> 
                        <a class="waves-effect waves-dark @if( in_array($croute, ['g.admin.index','g.admin.edit','g.admin.create']) ) active @endif" href="{{ route('g.admin.index') }}">
                            <i class="fa fa-user-secret"></i><span class="hide-menu">Administradores</span>
                        </a>
                    </li>
                    @if( $authUser->canDo('g.log.index') ) 
                    <li class="@if( in_array($croute, ['g.log.index']) ) active @endif"> 
                        <a class="waves-effect waves-dark" href="{{ route('g.log.index') }}">
                            <i class="fa fa-search"></i><span class="hide-menu">Registros</span>
                        </a>
                    </li>
                    @endif
                @endif
                
            </ul>
        </nav>
    </div>
</aside>


