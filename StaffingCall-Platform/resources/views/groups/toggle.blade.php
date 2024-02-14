<?php $url = url(Config('constants.urlVar.setGroupActiveDeactive').$results['id'].'/'.$results['status']); ?>

@if($results['status'])
<a id="A-<?= $results['id'] ?>" title="Click to De-activate" href="javascript:void(0);" 
                          onclick="published.toggle('A-<?= $results['id'] ?>', '<?= $url ?>')"
                        class="btn btn-sm btn-success mb-10">Active</a>
@else
<a id="A-<?= $results['id'] ?>" title="Click to Activate" href="javascript:void(0);" 
                          onclick="published.toggle('A-<?= $results['id'] ?>', '<?= $url ?>')"
                        class="btn btn-sm btn-danger mb-10">Deactive</a>
@endif  

<a href="{!! url(Config('constants.urlVar.editGroup').$results['id']) !!}" class="btn btn-sm btn-outline-info mb-10">
    <i class="fa fa-edit"></i></a>
<a title="Delete Group" onclick="return confirm('Do you really want to delete Group ?')" href="{!! url(Config('constants.urlVar.deleteGroup').$results['id']) !!}" 
                         class="btn btn-sm btn-danger mb-10"><i class="fa fa-trash"></i></a>