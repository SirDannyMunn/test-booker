<div class="panel">
    <div class="panel-heading">
        <h1>Users</h1>
    </div>
    <table class="table">
        <tr>
            <th>Name</th>
            <th>Tier</th>
            <th>Locations</th>
        </tr>

        @foreach($users as $user)
            <tr>
                <td>{{$user->name}}</td>
                <td>{{$user->tier}}</td>
                <td>
                <div>
                    <table>
                        <tr>
                            <th class="font-weight-bolder">{{$user->location}}</th>
                            @foreach($user->locations as $location)
{{--                                <th @if($user->location==$location->name)class="font-weight-bolder"@endif>{{$location->name}}</th>--}}
                                <th>{{$location->name}}</th>

                                @foreach($location->slots as $slot)
                                    {{$slot->datetime}}
                                @endforeach
                            @endforeach
                        </tr>
                    </table>
                </div>
                </td>
            </tr>
        @endforeach
    </table>
</div>

