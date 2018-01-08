<?php
    $columns = [
        'ID',
        'Identifier',
        'Parameters',
        'Group',
        'PageName',
        'Description',
        'LastChanged'
    ]
?>
<a href="{{route('translator.admin')}}">@t('Admin')</a>

<br/>
<br/>

<table border="1px" cellpadding="4" cellspacing="0">
    <tr>
        @foreach($columns as $key => $column)
            <td>@t($column)</td>
        @endforeach
    </tr>
    <tr>
        <td>{{$identifier->id}}</td>
        <td>{{$identifier->identifier}}</td>
        <td>{{implode(',', $identifier->parameters)}}</td>
        <td>{{$identifier->group}}</td>
        <td>{{$identifier->page_name}}</td>
        <td>{{$identifier->description}}</td>
        <td>{{$identifier->updated_at}}</td>
    </tr>
</table>

<hr>
<form action="" method="post">
    @foreach($available_locales as $locale)
        @php($translation = $identifier->translations->where('locale', $locale)->first())
        <span>{{$locale}}</span>
        @if(!empty($translation))
            <i style="font-size: smaller">{{$translation->updated_at}}</i>
            <br/>
            <textarea rows="4" cols="100" name="{{$locale}}">{{$translation->translation}}</textarea>
        @else
            <br/>
            <textarea rows="4" cols="100" name="{{$locale}}" placeholder="@t('Missing Translation')"></textarea>
        @endif
        <br/>
        <br/>
    @endforeach
    <input type="submit" value="@t('Save Changes')" />
    @isset($saved)
        <div style="display: inline;background-color: green;">
            @t('Saved Successfully')
        </div>
    @endisset
</form>
