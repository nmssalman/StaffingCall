<?php $url = url(Config('constants.urlVar.setUnitActiveDeactive').$results['id'].'/'.$results['status']); ?>

@if($results['status'])
<a id="A-<?= $results['id'] ?>" title="Click to De-activate" href="javascript:void(0);" 
                          onclick="published.toggle('A-<?= $results['id'] ?>', '<?= $url ?>')"
                        class="btn btn-sm btn-success mb-10">Active</a>
@else
<a id="A-<?= $results['id'] ?>" title="Click to Activate" href="javascript:void(0);" 
                          onclick="published.toggle('A-<?= $results['id'] ?>', '<?= $url ?>')"
                        class="btn btn-sm btn-danger mb-10">Deactive</a>
@endif  

<a href="{!! url(Config('constants.urlVar.editUnit').$results['id']) !!}" class="btn btn-sm btn-outline-info mb-10">
    Update</a>
     
 <a title="View detail" href="{!! url(Config('constants.urlVar.businessUnitDetail').$results['id']) !!}" class="btn btn-sm btn-outline-default mb-10">
     <i class="fa fa-eye"></i></a>
     

<a title="Delete Unit" onclick="return confirm('Do you really want to delete Unit ?')" href="{!! url(Config('constants.urlVar.deleteBusinessUnit').$results['id']) !!}" 
                         class="btn btn-sm btn-danger mb-10"><i class="fa fa-trash"></i></a>     