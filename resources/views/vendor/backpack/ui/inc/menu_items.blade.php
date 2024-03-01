{{-- This file is used for menu items by any Backpack v6 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>

<x-backpack::menu-item title="Users" icon="la la-question" :link="backpack_url('user')" />
<x-backpack::menu-item title="Plants" icon="la la-question" :link="backpack_url('plant')" />
<x-backpack::menu-item title="Locations" icon="la la-question" :link="backpack_url('location')" />
<x-backpack::menu-item title="Comments" icon="la la-question" :link="backpack_url('comment')" />
<x-backpack::menu-item title="Activity Logs" icon="la la-stream" :link="backpack_url('activity-log')" />
<x-backpack::menu-item title='Logs' icon='la la-terminal' :link="backpack_url('log')" />