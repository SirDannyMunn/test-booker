<div class="panel">
    <div class="panel-heading">
        <h1>Slots</h1>
    </div>
    <table class="table">
        <tr>
            <th>Location</th>
            <th>Datetime</th>
            <th>Taken</th>
        </tr>

        @foreach($slots as $slot)
            <tr>
                <td>{{$slot->location}}</td>
                <td>{{\Carbon\Carbon::parse($slot->datetime)->format('d/m/Y')}}</td>
                <td>{{($slot->taken) ? 'Yes' : 'No' }}</td>
            </tr>

            <tr>
                <th>Points</th>
                <th>Tries</th>
                <th>User ID</th>
            </tr>

            @foreach($slot->userSlots as $userSlot)
                <tr>
                    <td>{{$userSlot->points}}</td>
                    <td>{{$userSlot->tries}}</td>
                    <td>{{$userSlot->user_id}}</td>
                </tr>
            @endforeach
        @endforeach
    </table>
</div>
