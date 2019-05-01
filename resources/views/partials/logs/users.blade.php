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
                <span class="font-weight-bolder">{{$user->location}}</span>
                @foreach($user->locations as $location)
                    <span>{{$location->name}}</span>
                @endforeach
                </td>
            </tr>
        @endforeach
    </table>
</div>

