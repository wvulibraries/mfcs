<header class="applicationHeader">
    <div class="mfcs-logo">
        <img src="/images/mfcs.png" alt="Metadata Form Creation System" />
    </div>

    <div class="site-title">
        <h1>
            <span class="desktop"> Metadata Form Creation System </span>
            <span class="mobile"> MFCS </span>
        </h1>
    </div>

    <div class="projectsToggle">
        <a href="javascript:void(0)" class="projectToggle">
            <i class="fa fa-folder-open"></i> Select Projects
        </a>
    </div>
</header>

<section class="selectedProjects">
    <ul class="tags">
    </ul>
</section>

<!-- Modal - Select Current Projects -->
<div id="selectProjectsModal" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="selectProjectsModalWindow" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" aria-hidden="true">×</button>
        <h3>Your current projects:</h3>
    </div>
    <div class="modal-body">{local var="projectModalList"}</div>
    <div class="modal-footer">
        <button class="btn cancel" aria-hidden="true">Cancel</button>
        <button class="btn btn-primary submitProjects">Save changes</button>
    </div>
</div>

<div id="metadataModal" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="metadataModalWindow" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="metadataModalHeader"></h3>
    </div>
    <div class="modal-body" id="metadataModalBody"></div>
    <div class="modal-footer">
        <button class="btn"  class="close cancel" aria-hidden="true">Cancel</button>
        <button class="btn btn-primary saveMetadata">Save changes</button>
    </div>
</div>
