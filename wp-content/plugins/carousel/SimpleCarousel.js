/*
Script: SimpleCarousel.js

Builds a carousel object that manages the basic functions of a generic carousel (a carousel     here being a collection of "slides" that play from one to the next, with a collection of "buttons" that reference each slide).

License:
        http://clientside.cnet.com/wiki/cnet-libraries#license
*/
var SimpleCarousel = new MooClass({
        Implements: [MooOptions, MooEvents],
        options: {
//              onRotate: $empty,
//              onStop: $empty,
//              onAutoPlay: $empty,
//              onShowSlide: $empty,
                slideInterval: 4000,
                transitionDuration: 700,
                startIndex: 0,
                buttonOnClass: "selected",
                buttonOffClass: "off",
                rotateAction: "none",
                rotateActionDuration: 100,
                autoplay: true
        },
        initialize: function(container, slides, buttons, options){
                this.container = $moo(container);
                if(this.container.mooHasClass('hasCarousel')) return false;
                this.setOptions(options);
                this.container.mooAddClass('hasCarousel');
                this.slides = $$moo(slides);
                this.buttons = $$moo(buttons);
                this.createFx();
                this.showSlide(this.options.startIndex);
                if(this.options.autoplay) this.autoplay();
                if(this.options.rotateAction != 'none') this.setupAction(this.options.rotateAction);
                return this;
        },
        toElement: function(){
                return this.container;
        },
        setupAction: function(action) {
                this.buttons.mooEach(function(el, idx){
                        $moo(el).mooAddEvent(action, function() {
                                this.slideFx.setOptions(this.slideFx.options, {duration: this.options.rotateActionDuration});
                                if(this.currentSlide != idx) this.showSlide(idx);
                                this.stop();
                        }.mooBind(this));
                }, this);
        },
        createFx: function(){
                if (!this.slideFx) this.slideFx = new MooFx.Elements(this.slides, {duration: this.options.transitionDuration});
                this.slides.mooEach(function(slide){
                        slide.mooSetStyle('opacity',0);
                });
        },
        showSlide: function(slideIndex){
                var action = {};
                this.slides.mooEach(function(slide, index){
                        if(index == slideIndex && index != this.currentSlide){ //show
                                $moo(this.buttons[index]).mooAddClass(this.options.buttonOnClass);
                                $moo(this.buttons[index]).mooRemoveClass(this.options.buttonOffClass);

                                action[index.toString()] = {
                                        opacity: 1
                                };
                        } else {
                                $moo(this.buttons[index]).mooRemoveClass(this.options.buttonOnClass);
                                $moo(this.buttons[index]).mooAddClass(this.options.buttonOffClass);
                                action[index.toString()] = {
                                        opacity:0
                                };
                        }
                }, this);
                this.mooFireEvent('onShowSlide', slideIndex);
                this.currentSlide = slideIndex;
                this.slideFx.start(action);
                return this;
        },
        autoplay: function(){
                this.slideshowInt = this.rotate.periodical(this.options.slideInterval, this);
        this.mooFireEvent('onAutoPlay');
        return this;
    },
    stop: function(){
        $mooClear(this.slideshowInt);
        this.mooFireEvent('onStop');
        return this;
    },
    rotate: function(nextOrPrev){
        current = this.currentSlide;
        next = (current+1 >= this.slides.length) ? 0 : current+1;
        prev = (current-1 < 0) ? this.slides.length-1 : current-1;

        /* Hacky McHackerson? */
        if(nextOrPrev == 'next' || nextOrPrev == undefined){
            this.showSlide(next);
            this.mooFireEvent('onRotate', next);
        }else{
            this.showSlide(prev);
            this.mooFireEvent('onRotate', prev);
        }
        return this;
    },
    showVideo: function(videoDiv){
        videoDiv.mooSetStyle('display', 'block');
        this.slides.mooEach(function(slide){
            slide.mooSetStyles({
                'opacity': 1,
                'visibility': 'hidden'
            });
        });
    },

    hideVideo: function(videoDiv){
        videoDiv.mooSetStyle('display', 'none');
        var current = this.currentSlide;
        this.slides.mooEach(function(slide, index){
            if(index != current){
                slide.mooSetStyle('opacity', 0);
            };
            slide.mooSetStyle('visibility', 'visible');
        });
    }
});

