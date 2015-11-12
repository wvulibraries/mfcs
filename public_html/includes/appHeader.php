<header class="applicationHeader">
    <div class="widthContainer">
        <div class="mfcs-logo">
            <img src="/images/mfcs.png" alt="Metadata Form Creation System" />
        </div>

        <div class="site-title">
            <h1>
                <span class="desktop"> Metadata Form Creation System </span>
                <span class="mobile"> MFCS </span>
            </h1>
        </div>

        <div class="navToggle">
            <a href="javascript:void(0)" class="toggleNav btn btn-primary">
                <i class="fa fa-bars"></i> Menu
            </a>
        </div>

        <div class="projectsToggle">
            <a href="javascript:void(0)" class="projectToggle btn btn-primary">
                <i class="fa fa-folder-open"></i> Select Projects
            </a>
        </div>

        <section class="selectedProjects">
            <ul class="tags">
            </ul>
        </section>
    </div>
</header>



<!-- Modal - Select Current Projects -->
<div id="selectProjectsModal" class="menuModal hide" tabindex="-1" role="dialog" aria-labelledby="selectProjectsModalWindow" aria-hidden="true">
    <div class="modalContainer">
        <div class="modal-header">
            <button type="button" class="close" aria-hidden="true">×</button>
            <h3>Your current projects:</h3>
        </div>
        <div class="modal-body"> {local var="projectModalList"} </div>
        <div class="modal-footer">
            <button class="btn cancel" aria-hidden="true">Cancel</button>
            <button class="btn btn-primary submitProjects">Save changes</button>
        </div>
    </div>
</div>

<div id="metadataModal" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="metadataModalWindow" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="metadataModalHeader"></h3>
    </div>
    <div class="modalbody" id="metadataModalBody">
        <div class="loading">
            <i class="fa fa-refresh fa-spin fa-4x"></i>
            <p> Loading content ... </p>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn cancelButton">Cancel</button>
        <button class="btn btn-primary saveMetadata">Save changes</button>
    </div>
</div>

<div class="bgCloak">
</div>
