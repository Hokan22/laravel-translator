<?php
    /** @var array $columns Columns to display in the overview table */
    $columns = [
        'ID',
        'Identifier',
        'Page link',
        'Description',
        'locales' => $available_locales,
        'LastChanged'
    ]
?>

<style>
    li  {
        display: inline;
    }
</style>


<form action="{{route('translator.admin')}}" method="get" style="display: inline">
    <input type="hidden" name="locale" value="{{$query_locale}}">
    <input type="search" name="search" placeholder="search" value="{{$search}}"/><span style="font-size: x-large"> &#x1F50E;</span>
</form>

@if(session('translation_live_mode'))
    <a href="{{route('translator.change.live_mode', ['state' => 'disable'])}}">Disable Live Mode</a>
@else
    <a href="{{route('translator.change.live_mode', ['state' => 'enable'])}}">Enable Live Mode</a>
@endif

<hr />

<table border="1px" cellpadding="4" cellspacing="0" width="100%">
    <tr>
        @foreach($columns as $key => $column)
            @if(!is_array($column))
                <td>
                </td>
            @else
                @foreach($column as $locale)
                    @if(app('request')->input('locale') === $locale)
                        <td><a href="{{ route('translator.admin', ['search' =>  $search])}}">@t('Show All')</a></td>
                    @else
                        <td><a href="{{ route('translator.admin', ['locale' => $locale, 'search' =>  $search])}}">@t('Show Missing')</a></td>
                    @endif
                @endforeach
            @endif
        @endforeach
    </tr>

    <tr>
        @foreach($columns as $key => $column)
            @if(is_array($column))
                @foreach($column as $locale)
                    <td>{{$locale}}</td>
                @endforeach
            @else
                 <td>@t($column)</td>
            @endif
        @endforeach
    </tr>
    <form action="{{ route('translator.admin', ['search' => $search]) }}" method="post">
        {{ csrf_field() }}
        @foreach($identifier as $ident)
            <tr>

                <td><a href="{{route('translator.admin.edit', ['id' => $ident->id, 'page' => $identifier->currentPage(), 'locale' => $query_locale, 'search' => $search])}}">{{$ident->id}}</a></td>
                <td>{{ $ident->identifier }}<input type="hidden" name="{{$ident->id}}[identifier]" value="{{$ident->identifier}}" /></td>
                <td><a target="_blank" href="{{config('url')."/".$ident->page_name}}">{{$ident->page_name}}</a>
                <td><input name="{{$ident->id}}[description]" value="{{$ident->description}}"/></td>
                @foreach($available_locales as $locale)
                    <td>{!! '<span '. ($ident->translations->where('locale', $locale)->count() === 1 ?  'style="color:green;">✔' : 'style="color:red;">✘') . '<span/>' !!}</td>
                @endforeach
                <td>{{$ident->updated_at}}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="{{count($columns)+count($available_locales)-2}}">
                {{$identifier->links()}}
            </td>
            <td >
                <input type="submit" value="Save Changes" />
            </td>
        </tr>
    </form>
</table>

