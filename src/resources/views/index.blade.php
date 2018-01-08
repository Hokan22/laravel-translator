<?php
    $columns = [
        'ID',
        'Identifier',
        'Parameters',
        'Group',
        'PageName',
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

<form action="{{route('translator.admin')}}" method="get">
    <input type="search" name="search" placeholder="@t('search')" /><span style="font-size: x-large"> &#x1F50E;</span>
</form>

<table border="1px" cellpadding="4" cellspacing="0" width="100%">
    <tr>
        @foreach($columns as $key => $column)
            @if(!is_array($column))
                <td>
                </td>
            @else
                @foreach($column as $locale)
                    @if(app('request')->input('locale') === $locale)
                        <td><a href="{{ route('translator.admin', ['page' => $identifier->currentPage()])}}">@t('Show All')</a></td>
                    @else
                        <td><a href="{{ route('translator.admin', ['locale' => $locale, 'page' => $identifier->currentPage()])}}">@t('Show Missing')</a></td>
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

    <form action="{{ route('translator.admin', ['search' => app('request')->input('search')]) }}" method="post">
        @foreach($identifier as $ident)
            <tr>
                <td><a href="{{route('translator.admin.edit', ['id' => $ident->id])}}">{{$ident->id}}</a></td>
                <td>{{ $ident->identifier }}<input type="hidden" name="{{$ident->id}}[identifier]" value="{{$ident->identifier}}" /></td>
                <td><input name="{{$ident->id}}[parameters]" value="{{implode(',', $ident->parameters)}}" /></td>
                <td><input name="{{$ident->id}}[group]" value="{{$ident->group}}"/></td>
                <td><input name="{{$ident->id}}[page_name]" value="{{$ident->page_name}}"/></td>
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
                <input type="submit" value="@t('Save Changes')" />
            </td>
        </tr>
    </form>
</table>

