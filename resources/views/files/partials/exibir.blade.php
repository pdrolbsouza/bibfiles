<div class=row style="border: 10px;"> 
{{ $files->appends(request()->query())->links() }}
@can('admin') 
<a href="/fileExcel/?busca={{ request()->get('busca') }}" class="btn btn-info" style="position: absolute; right: 0; margin: 5px;">
<i class="fa fa-file" aria-hidden="true"></i> Exportar planilha </a>
@endcan('admin')
</div>

<table class="table table-striped">
    <thead>
            <tr>
            <th>Nome</th>
            <th>Arquivo</th>
        @can('admin')
            <th>Download (Apenas Administrador)</th>
            <th>Ações</th>
        @endcan('admin') 
</thead>

<tbody>
        @foreach($files as $arquivo)
            <tr data-file-name="{{ $arquivo->name }}">
                <td>{{$arquivo->name}}</td>
                <td>            
                <a href="/{{$arquivo->original_name}}" type="application/pdf" target="pdf-frame"><i class="fas fa-file-pdf"></i> {{$arquivo->original_name}} </a>
                </td>
        @can('admin')
                <td>
                    <a href="/files/{{$arquivo->id}}" type="application/pdf" target="pdf-frame"><i class="fas fa-file-pdf"></i></a>
                <td>

            <form method="post" action="/files/{{$arquivo->id}}">         
            @csrf
            @method('delete')
                    <button class="botao" type="submit" onclick="return confirm('Tem certeza que deseja deletar?')"><i class="fas fa-trash-alt"></i></button>
            </form></td>
        @endcan('admin')
            </tr>
        @endforeach
    </tbody>
</table>

{{ $files->appends(request()->query())->links() }}
